<?php

namespace App\Http\Controllers;

use App\Models\ChallanHead;
use App\Models\Challans;
use App\Models\Classes;
use App\Models\ClassWiseFee;
use App\Models\Concession;
use App\Models\ConcessionPolicyHead;
use App\Models\FeeHead;
use App\Models\JournalItem;
use App\Models\Registring_option;
use App\Models\Session;
use App\Models\Section;
use App\Models\StudentEnrollments;
use App\Models\StudentFeeStructure;
use App\Models\StudentFeeRevisionBatch;
use App\Models\StudentFeeRevisionItem;
use App\Models\StudentHistory;
use App\Models\StudentReadmission;
use App\Models\StudentRegistration;
use App\Models\StudentTransfer;
use App\Models\StudentWithdrawal;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Auth;
use DB;

class StudentReadmissionController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->type === 'company') {
            $baseQuery = StudentReadmission::with('student', 'branch', 'class', 'session', 'challan')
                ->where('created_by', Auth::user()->creatorId());
        } else {
            $baseQuery = StudentReadmission::with('student', 'branch', 'class', 'session', 'challan')
                ->where('owned_by', Auth::user()->ownedId());
        }

        // Summary counts are calculated before filters so the cards always represent
        // the complete readmission workload visible to the current user.
        $summary = [
            'total' => (clone $baseQuery)->count(),
            'for_approval' => (clone $baseQuery)
                ->whereRaw("LOWER(TRIM(status)) = ?", ['for approval'])
                ->count(),
            'approved' => (clone $baseQuery)
                ->whereRaw("LOWER(TRIM(status)) = ?", ['approved'])
                ->count(),
            'rejected' => (clone $baseQuery)
                ->whereRaw("LOWER(TRIM(status)) IN (?, ?)", ['rejected', 'rollbacked'])
                ->count(),
        ];

        $query = clone $baseQuery;

        if ($request->filled('status')) {
            $query->whereRaw(
                "LOWER(TRIM(status)) = ?",
                [strtolower(trim((string) $request->status))]
            );
        }

        if ($request->filled('flow_type')) {
            $query->where('flow_type', $request->flow_type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('readmission_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('readmission_date', '<=', $request->to_date);
        }

        $studenttransfer = $query
            ->orderByRaw(
                "CASE
                    WHEN LOWER(TRIM(status)) = 'for approval' THEN 0
                    WHEN LOWER(TRIM(status)) = 'approved' THEN 1
                    WHEN LOWER(TRIM(status)) = 'rejected' THEN 2
                    ELSE 3
                END"
            )
            ->orderByDesc('id')
            ->get();

        $statusOptions = [
            '' => 'All Statuses',
            'for approval' => 'For Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'rollbacked' => 'Rollbacked',
            'canceled' => 'Canceled',
        ];

        $flowOptions = [
            '' => 'All Scenarios',
            'reactivation' => 'Reactivation',
            'readmission' => 'Readmission',
            're_enrollment' => 'Re-enrollment',
        ];

        return view(
            'students.student_readmission.index',
            compact(
                'studenttransfer',
                'summary',
                'statusOptions',
                'flowOptions',
                'request'
            )
        );
    }
    public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | Branch selectors
        |--------------------------------------------------------------------------
        |
        | Company can select any branch under the tenant.
        | Branch users now also see the tenant branches, with their own branch
        | selected by default in the Blade.
        |
        */
        $branches = User::where('type', 'branch')
            ->where('created_by', \Auth::user()->creatorId())
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id');

        if (\Auth::user()->type === 'company') {
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        }

        $branches->prepend('Select Branch', '');

        $session = Session::where('created_by', \Auth::user()->creatorId())
            ->get()
            ->pluck('year', 'id');
        $session->prepend('Select Session', '');

        $activeSessionId = Session::where('created_by', \Auth::user()->creatorId())
            ->where('active_status', 1)
            ->orderBy('id', 'desc')
            ->value('id');

        return view(
            'students.student_readmission.create',
            compact('branches', 'session', 'activeSessionId')
        );
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validator = \Validator::make($request->all(), [
            'student_id' => 'required',
            'branch_id' => 'required',
            'class_id' => 'required',
            'session_id' => 'required',
            'new_branch_id' => 'required',
            'new_class_id' => 'required',
            'new_session_id' => 'required',
            'new_section_id' => 'required',
            'flow_type' => 'nullable|string',
            'readmission_date' => 'required|date',
            'month_date' => 'required',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'reason' => 'required|string',
            'selected_heads' => 'nullable|array',
            'generate_gap_months' => 'nullable|boolean',
            'gap_months' => 'nullable|array',
            'gap_months.*' => 'date',
            'tuition_increment_enabled' => 'nullable|boolean',
            'tuition_increment_percentage' => 'nullable|integer|min:0',
            'tuition_increment_effective_from' => 'nullable|date_format:Y-m',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', $validator->getMessageBag()->first())
                ->withInput();
        }

        $context = $this->resolveReadmissionContext($request->student_id);
        if (!$context) {
            return redirect()->back()
                ->with('error', 'Student readmission context could not be loaded. Please verify that a withdrawal record exists for this student.')
                ->withInput();
        }

        $student = $context['student'];
        $enrollment = $context['enrollment'];
        $withdrawal = $context['withdrawal'];
        $flowType = $this->readmissionFlowType($withdrawal, $request->flow_type);

        if (!$flowType) {
            return redirect()->back()
                ->with('error', 'The selected withdrawal is not eligible for this readmission scenario.')
                ->withInput();
        }

        // The 30-day Reactivation restriction is a BRANCH control only.
        // Company users may manually choose Reactivation even outside the 30-day window.
        if (
            \Auth::user()->type === 'branch'
            && $flowType === 'reactivation'
            && !$this->isWithinReactivationWindow($withdrawal)
        ) {
            return redirect()->back()
                ->with('error', 'Reactivation is only allowed within 30 days of withdrawal.')
                ->withInput();
        }

        $billingMonth = $this->readmissionBillingMonth($request->month_date);
        $sourcePlacement = $this->sourcePlacement($student, $enrollment);
        $targetPlacement = [
            'branch_id' => (int) ($request->new_branch_id ?: $request->branch_id),
            'class_id' => (int) ($request->new_class_id ?: $request->class_id),
            'session_id' => (int) ($request->new_session_id ?: $request->session_id),
            'section_id' => (int) ($request->new_section_id ?: 0),
        ];

        $selectedHeadIds = collect($request->input('selected_heads', []))
            ->map(function ($headId) {
                return (int) $headId;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $snapshot = $this->buildApplicationSnapshot(
            $request,
            $context,
            $flowType,
            $sourcePlacement,
            $targetPlacement,
            $billingMonth,
            $selectedHeadIds
        );

        if ($flowType !== 'reactivation') {
            $targetHeads = collect($snapshot['target_fee_structure'] ?? []);
            $selectedHeadIds = $this->mergeLockedReadmissionHeadIds(
                $targetHeads->all(),
                $selectedHeadIds,
                $flowType
            );

            if (empty($selectedHeadIds)) {
                return redirect()->back()
                    ->with('error', 'Please select at least one fee head from the applicable fee structure.')
                    ->withInput();
            }

            $availableHeadIds = $targetHeads->pluck('head_id')->map(fn($id) => (int) $id);
            $invalidHeads = collect($selectedHeadIds)->diff($availableHeadIds);
            if ($invalidHeads->isNotEmpty()) {
                return redirect()->back()
                    ->with('error', 'One or more selected fee heads are no longer available. Refresh the preview and try again.')
                    ->withInput();
            }

            $snapshot['selected_head_ids'] = array_values($selectedHeadIds);
        }

        try {
            $createdChallanIds = [];

            DB::transaction(function () use ($request, $student, $enrollment, $withdrawal, $flowType, $billingMonth, $targetPlacement, &$snapshot, &$createdChallanIds) {

                if ($flowType === 'reactivation') {
                    $this->reactivateFromReadmission(
                        $withdrawal,
                        $request->readmission_date,
                        $request->reason
                    );

                    // Reactivation is a branch-level immediate action.
                    // IMPORTANT: Regular challans must use ONLY fee heads that are
                    // currently checked in StudentFeeStructure. Do not trust the
                    // submitted selected_heads request for this branch-side flow.
                    $checkedCurrentHeadIds = StudentFeeStructure::where('reg_id', $student->id)
                        ->where('checked_status', 1)
                        ->pluck('head_id')
                        ->map(function ($headId) {
                            return (int) $headId;
                        })
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    $regularRows = collect($snapshot['existing_fee_structure'] ?? [])
                        ->filter(function ($row) use ($checkedCurrentHeadIds) {
                            return in_array(
                                (int) ($row['head_id'] ?? 0),
                                $checkedCurrentHeadIds,
                                true
                            );
                        })
                        ->values()
                        ->all();

                    // Keep the audit snapshot aligned with the actual DB selection
                    // that was used to build the Reactivation Regular challans.
                    $snapshot['existing_selected_head_ids'] = $checkedCurrentHeadIds;

                    if (empty($regularRows)) {
                        throw new \RuntimeException(
                            'No checked fee heads were found in the student current fee structure. Reactivation challan generation stopped.'
                        );
                    }

                    $sourcePlacement = $snapshot['source_placement'] ?? [];

                    foreach ($snapshot['gap_months'] ?? [] as $monthDate) {
                        $challan = $this->generateRegularChallanFromSnapshot(
                            $student,
                            $sourcePlacement,
                            $monthDate,
                            $regularRows,
                            $snapshot['active_policy'] ?? [],
                            false
                        );

                        if ($challan) {
                            $createdChallanIds[] = $challan->id;
                        }
                    }

                    $record = new StudentReadmission();
                    $record->student_id = optional($enrollment)->enrollId ?: $student->roll_no ?: $student->id;
                    $record->challan_id = $createdChallanIds[0] ?? 0;
                    $record->readmission_date = $request->readmission_date;
                    $record->branch_id = $sourcePlacement['branch_id'] ?? $targetPlacement['branch_id'];
                    $record->class_id = $sourcePlacement['class_id'] ?? $targetPlacement['class_id'];
                    $record->remarks = 'Reactivation: ' . $request->reason;
                    $record->status = 'approved';
                    $record->session_id = $sourcePlacement['session_id'] ?? $targetPlacement['session_id'];
                    $record->owned_by = optional($enrollment)->owned_by ?: $student->owned_by ?: $student->branch;
                    $record->created_by = \Auth::user()->creatorId();

                    $this->fillSnapshotColumns(
                        $record,
                        $flowType,
                        $sourcePlacement,
                        true,
                        $snapshot,
                        $createdChallanIds
                    );

                    $record->approved_by = \Auth::id();
                    $record->approved_at = now();
                    $record->save();

                    return;
                }

                // Readmission and Re-enrollment are approval based.
                // Do not mutate enrollment, fee structure, history, or challans here.
                $record = new StudentReadmission();
                $record->student_id = optional($enrollment)->enrollId ?: $student->roll_no ?: $student->id;
                $record->challan_id = 0;
                $record->readmission_date = $request->readmission_date;
                $record->branch_id = $targetPlacement['branch_id'];
                $record->class_id = $targetPlacement['class_id'];
                $record->remarks = $this->readmissionFlowLabel($flowType) . ': ' . $request->reason;
                $record->status = 'for approval';
                $record->session_id = $targetPlacement['session_id'];
                $record->owned_by = optional($enrollment)->owned_by ?: $student->owned_by ?: $student->branch;
                $record->created_by = \Auth::user()->creatorId();

                $this->fillSnapshotColumns(
                    $record,
                    $flowType,
                    $targetPlacement,
                    !empty($snapshot['selected_gap_months']),
                    $snapshot,
                    []
                );

                $record->save();
            });
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }

        if ($flowType === 'reactivation') {
            $message = 'Student reactivation completed successfully.';
            if (!empty($createdChallanIds)) {
                $message .= ' ' . count($createdChallanIds) . ' missing Regular challan(s) were generated.';
            }

            return redirect()->route('readmissionstudent.index')->with('success', $message);
        }

        return redirect()->route('readmissionstudent.index')
            ->with(
                'success',
                $this->readmissionFlowLabel($flowType) . ' application has been sent for company approval. No challan has been generated yet.'
            );
    }

    public function show($id)
    {
        $record = StudentReadmission::with('student', 'branch', 'class', 'session', 'challan')
            ->findOrFail($id);

        if (\Auth::user()->type !== 'company'
            && (int) $record->owned_by !== (int) \Auth::user()->ownedId()) {
            abort(403);
        }

        if (\Auth::user()->type === 'company'
            && (int) $record->created_by !== (int) \Auth::user()->creatorId()) {
            abort(403);
        }

        $snapshot = $this->decodeJsonColumn($record->snapshot);
        $editable = \Auth::user()->type === 'company'
            && strtolower(trim((string) $record->status)) === 'for approval';

        /*
         * Approved preview: expose every challan generated by HO approval.
         * Main challan + any selected gap Regular challans.
         */
        $generatedChallans = $this->generatedReadmissionChallans(
            $record
        );

        $reviewBranches = collect();
        $reviewSessions = collect();
        $reviewClasses = collect();
        $reviewSections = collect();

        if (\Auth::user()->type === 'company') {
            $reviewBranches = User::where('type', 'branch')
                ->where('created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $reviewBranches->prepend(\Auth::user()->name, \Auth::user()->id);

            $reviewSessions = Session::where('created_by', \Auth::user()->creatorId())
                ->orderByDesc('id')
                ->get()
                ->pluck('year', 'id');

            $target = $snapshot['target_placement'] ?? [];
            $targetBranchId = (int) ($target['branch_id'] ?? $record->branch_id);
            $targetClassId = (int) ($target['class_id'] ?? $record->class_id);

            $reviewClasses = Classes::where('owned_by', $targetBranchId)
                ->where('created_by', \Auth::user()->creatorId())
                ->where('active_status', 1)
                ->get()
                ->pluck('name', 'id');

            $targetSectionId = (int) (
                $target['section_id']
                ?? $record->target_section_id
                ?? 0
            );

            $reviewSections = collect();

            if ($targetSectionId) {
                $reviewSections = Section::where(
                    'id',
                    $targetSectionId
                )
                    ->get()
                    ->pluck('name', 'id');
            }
        }

        return view(
            'students.student_readmission.review',
            compact(
                'record',
                'snapshot',
                'editable',
                'reviewBranches',
                'reviewSessions',
                'reviewClasses',
                'reviewSections',
                'generatedChallans'
            )
        );
    }

    /**
     * Company-only editor for a pending snapshot.
     * Branch submissions remain immutable from the branch side; company can adjust
     * the final target placement, dates, selected target heads and selected gap months.
     */
    public function updateReview(Request $request, $id)
    {
        if (\Auth::user()->type !== 'company') {
            abort(403);
        }

        $record = StudentReadmission::findOrFail($id);
        if ((int) $record->created_by !== (int) \Auth::user()->creatorId()) {
            abort(403);
        }

        if (strtolower(trim((string) $record->status)) !== 'for approval') {
            return redirect()->route('readmissionstudent.show', $record->id)
                ->with('error', 'Approved/rejected applications are preview-only and cannot be edited.');
        }

        $validator = \Validator::make($request->all(), [
            'flow_type' => 'required|in:readmission,re_enrollment',
            'new_branch_id' => 'required|integer',
            'new_session_id' => 'required|integer',
            'new_class_id' => 'required|integer',
            'new_section_id' => 'required|integer',
            'readmission_date' => 'required|date',
            'month_date' => 'required',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'reason' => 'required|string|max:2000',
            'selected_heads' => 'required|array|min:1',
            'selected_heads.*' => 'integer',
            'current_structure_present' => 'nullable|boolean',
            'current_selected_heads' => 'nullable|array',
            'current_selected_heads.*' => 'integer',
            'gap_months' => 'nullable|array',
            'gap_months.*' => 'date',
            'approve_after_save' => 'nullable|boolean',
            'tuition_increment_enabled' => 'nullable|boolean',
            'tuition_increment_percentage' => 'nullable|integer|min:0',
            'tuition_increment_effective_from' => 'nullable|date_format:Y-m',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', $validator->getMessageBag()->first())
                ->withInput();
        }

        $snapshot = $this->decodeJsonColumn($record->snapshot);
        $studentId = $snapshot['student']['reg_id'] ?? null;
        $student = StudentRegistration::findOrFail($studentId);
        $enrollment = StudentEnrollments::where('regId', $student->id)->orderByDesc('id')->first();
        $withdrawalId = $snapshot['withdrawal']['id'] ?? null;
        $withdrawal = $withdrawalId
            ? StudentWithdrawal::findOrFail($withdrawalId)
            : StudentWithdrawal::where('student_id', $student->id)->orderByDesc('id')->firstOrFail();

        $sourcePlacement = [
            'branch_id' => (int) (
                $snapshot['source_placement']['branch_id']
                ?? optional($enrollment)->owned_by
                ?? $student->owned_by
                ?? $student->branch
            ),
            'class_id' => (int) (
                $snapshot['source_placement']['class_id']
                ?? optional($enrollment)->class_id
                ?? $student->class_id
            ),
            'session_id' => (int) (
                $snapshot['source_placement']['session_id']
                ?? optional($enrollment)->session_id
                ?? $student->session_id
            ),
            'section_id' => (int) (
                $snapshot['source_placement']['section_id']
                ?? optional($enrollment)->section_id
                ?? $student->section_id
            ),
        ];

        $targetPlacement = [
            'branch_id' => (int) $request->new_branch_id,
            'class_id' => (int) $request->new_class_id,
            'session_id' => (int) $request->new_session_id,
            'section_id' => (int) $request->new_section_id,
        ];

        $selectedHeadIds = collect($request->input('selected_heads', []))
            ->map(function ($id) { return (int) $id; })
            ->filter()
            ->unique()
            ->values()
            ->all();

        // The current structure has its own selection. It is separate from the
        // target admission/re-admission heads. Save Review / Approve also honors
        // these checkboxes, so company cannot accidentally approve stale current heads.
        $currentSelectedHeadIds = collect($request->input('current_selected_heads', []))
            ->map(function ($id) { return (int) $id; })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($request->boolean('current_structure_present')) {
            DB::transaction(function () use ($student, $sourcePlacement, $snapshot, $currentSelectedHeadIds) {
                foreach (($snapshot['existing_fee_structure'] ?? []) as $row) {
                    $headId = (int) ($row['head_id'] ?? 0);
                    if (!$headId) {
                        continue;
                    }

                    $structure = StudentFeeStructure::where('reg_id', $student->id)
                        ->where('head_id', $headId)
                        ->orderByDesc('id')
                        ->first();

                    if (!$structure) {
                        $structure = new StudentFeeStructure();
                        $structure->reg_id = $student->id;
                        $structure->student_id = $student->roll_no;
                        $structure->branch_id = (int) $sourcePlacement['branch_id'];
                        $structure->class_id = (int) $sourcePlacement['class_id'];
                        $structure->head_id = $headId;
                        $structure->owned_by = (int) $sourcePlacement['branch_id'];
                        $structure->created_by = $student->created_by;
                    }

                    $structure->amount = (float) ($row['class_amount'] ?? 0);
                    $structure->discount = (float) ($row['base_discount'] ?? 0);
                    $structure->checked_status = in_array(
                        $headId,
                        $currentSelectedHeadIds,
                        true
                    ) ? 1 : 0;
                    $structure->save();
                }

                StudentFeeStructure::where('reg_id', $student->id)
                    ->update(['checked_status' => 0]);

                if (!empty($currentSelectedHeadIds)) {
                    StudentFeeStructure::where('reg_id', $student->id)
                        ->whereIn('head_id', $currentSelectedHeadIds)
                        ->update(['checked_status' => 1]);
                }
            });
        }

        list($policyHeads, $activeConcession) = $this->activeConcessionPolicyMap($student->id);
        $existingStructure = $this->existingFeeStructurePreview(
            $student,
            $sourcePlacement,
            $policyHeads
        );
        $tuitionIncrementEnabled = $request->boolean(
            'tuition_increment_enabled',
            true
        );
        $tuitionIncrementPercentage = max(
            0,
            (int) $request->input('tuition_increment_percentage', 0)
        );

        /*
         * Billing Month is the effective month for Tuition revision.
         */
        $billingMonth = $this->readmissionBillingMonth(
            $request->month_date
        );

        $targetStructure = $this->targetFeeStructurePreview(
            $student,
            $sourcePlacement,
            $targetPlacement,
            $request->flow_type,
            $policyHeads,
            $tuitionIncrementPercentage,
            $tuitionIncrementEnabled
        );

        $feeRevision = $this->tuitionRevisionPreview(
            $student,
            $sourcePlacement,
            $targetPlacement,
            $policyHeads,
            $tuitionIncrementPercentage,
            $tuitionIncrementEnabled
        );

        $tuitionIncrementEffectiveFrom =
            $this->tuitionIncrementEffectiveFrom(
                $request->input('tuition_increment_effective_from'),
                $billingMonth
            );

        $feeRevision['effective_from'] =
            $tuitionIncrementEffectiveFrom;

        $selectedHeadIds = $this->mergeLockedReadmissionHeadIds(
            $targetStructure,
            $selectedHeadIds,
            $request->flow_type
        );

        $availableTargetIds = collect($targetStructure)
            ->pluck('head_id')
            ->map(function ($id) { return (int) $id; });
        $invalid = collect($selectedHeadIds)->diff($availableTargetIds);
        if ($invalid->isNotEmpty()) {
            return redirect()->back()->with(
                'error',
                'The target placement changed and one or more selected fee heads are no longer available. Refresh the target fee structure and select again.'
            )->withInput();
        }

        $gapBilling = $this->gapBillingContext(
            $student->id,
            $withdrawal
        );

        $gapBilling = $this->applyReadmissionBillingMonthGapRule(
            $gapBilling,
            $request->flow_type,
            $billingMonth
        );

        $allowedGapMonths = collect($gapBilling['months'] ?? []);
        $selectedGapMonths = collect($request->input('gap_months', []))
            ->map(function ($month) {
                return Carbon::parse($month)->startOfMonth()->toDateString();
            })
            ->filter(function ($month) use ($allowedGapMonths) {
                return $allowedGapMonths->contains($month);
            })
            ->unique()
            ->values()
            ->all();

        $snapshot['version'] = 7;
        $snapshot['company_reviewed_at'] = now()->toISOString();
        $snapshot['company_reviewed_by'] = [
            'id' => \Auth::id(),
            'name' => \Auth::user()->name,
        ];
        $snapshot['flow']['type'] = $request->flow_type;
        $snapshot['flow']['label'] = $this->readmissionFlowLabel($request->flow_type);
        $snapshot['target_placement'] = $this->placementDetails($targetPlacement);
        $snapshot['existing_fee_structure'] = $existingStructure;
        $snapshot['existing_selected_head_ids'] = collect($existingStructure)
            ->filter(function ($row) {
                return (int) ($row['checked'] ?? 0) === 1;
            })
            ->pluck('head_id')
            ->map(function ($id) { return (int) $id; })
            ->values()
            ->all();
        $snapshot['target_fee_structure'] = $targetStructure;
        $snapshot['selected_head_ids'] = array_values($selectedHeadIds);
        $snapshot['fee_revision'] = $feeRevision;
        $snapshot['active_policy'] = $this->previewPolicyDetails($activeConcession, $policyHeads);
        $snapshot['latest_policy_application'] = $this->previewLatestPolicyDetails(
            $this->latestConcessionApplication($student->id)
        );
        $snapshot['gap_months'] = $gapBilling['months'];
        $snapshot['gap_month_statuses'] = $gapBilling['month_statuses'];
        $snapshot['gap_range'] = $gapBilling['range'];
        $snapshot['selected_gap_months'] = $selectedGapMonths;
        $snapshot['generate_gap_months'] = !empty($selectedGapMonths);
        $snapshot['request'] = [
            'readmission_date' => Carbon::parse($request->readmission_date)->toDateString(),
            'billing_month' => $billingMonth,
            'issue_date' => Carbon::parse($request->issue_date)->toDateString(),
            'due_date' => Carbon::parse($request->due_date)->toDateString(),
            'reason' => $request->reason,
            'tuition_increment_enabled' => (bool) $tuitionIncrementEnabled,
            'tuition_increment_percentage' => (int) $tuitionIncrementPercentage,
            'tuition_increment_effective_from' => $tuitionIncrementEffectiveFrom,
        ];

        $record->flow_type = $request->flow_type;
        $record->branch_id = $targetPlacement['branch_id'];
        $record->class_id = $targetPlacement['class_id'];
        $record->session_id = $targetPlacement['session_id'];
        $record->target_section_id = $targetPlacement['section_id'];
        $record->readmission_date = $request->readmission_date;
        $record->remarks = $this->readmissionFlowLabel($request->flow_type) . ': ' . $request->reason;
        $record->generate_gap_months = !empty($selectedGapMonths);
        $record->snapshot = json_encode($snapshot);
        $record->save();

        if ($request->boolean('approve_after_save')) {
            return $this->changeStatus($record->id, 'approved');
        }

        return redirect()->route('readmissionstudent.show', $record->id)
            ->with('success', 'Company review changes saved successfully.');
    }

    /**
     * Company-only save for CURRENT StudentFeeStructure checked_status.
     * This is intentionally separate from the pending application snapshot editor.
     */
    public function saveCurrentFeeStructure(Request $request, $id)
    {
        if (\Auth::user()->type !== 'company') {
            return response()->json(['success' => false, 'message' => 'Only company users can update the current structure from review.'], 403);
        }

        $record = StudentReadmission::findOrFail($id);
        if ((int) $record->created_by !== (int) \Auth::user()->creatorId()) {
            abort(403);
        }

        if (strtolower(trim((string) $record->status)) !== 'for approval') {
            return response()->json([
                'success' => false,
                'message' => 'This application is no longer editable.'
            ], 422);
        }

        $validator = \Validator::make($request->all(), [
            'selected_heads' => 'nullable|array',
            'selected_heads.*' => 'integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->getMessageBag()->first()], 422);
        }

        $snapshot = $this->decodeJsonColumn($record->snapshot);
        $student = StudentRegistration::findOrFail($snapshot['student']['reg_id'] ?? 0);
        $source = $snapshot['source_placement'] ?? [];
        $selected = collect($request->input('selected_heads', []))
            ->map(function ($id) { return (int) $id; })
            ->filter()
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($student, $source, $selected, &$snapshot) {
            $rows = $snapshot['existing_fee_structure'] ?? [];

            // Ensure every displayed current row exists as a StudentFeeStructure row.
            foreach ($rows as $row) {
                $headId = (int) ($row['head_id'] ?? 0);
                if (!$headId) {
                    continue;
                }

                $structure = StudentFeeStructure::where('reg_id', $student->id)
                    ->where('head_id', $headId)
                    ->orderByDesc('id')
                    ->first();

                if (!$structure) {
                    $structure = new StudentFeeStructure();
                    $structure->reg_id = $student->id;
                    $structure->student_id = $student->roll_no;
                    $structure->branch_id = (int) ($source['branch_id'] ?? $student->owned_by);
                    $structure->class_id = (int) ($source['class_id'] ?? $student->class_id);
                    $structure->head_id = $headId;
                    $structure->owned_by = (int) ($source['branch_id'] ?? $student->owned_by);
                    $structure->created_by = $student->created_by;
                }

                $structure->amount = (float) ($row['class_amount'] ?? 0);
                $structure->discount = (float) ($row['base_discount'] ?? 0);
                $structure->checked_status = in_array($headId, $selected, true) ? 1 : 0;
                $structure->save();
            }

            StudentFeeStructure::where('reg_id', $student->id)
                ->whereNotIn('head_id', $selected ?: [0])
                ->update(['checked_status' => 0]);

            if (!empty($selected)) {
                StudentFeeStructure::where('reg_id', $student->id)
                    ->whereIn('head_id', $selected)
                    ->update(['checked_status' => 1]);
            }

            list($policyHeads,) = $this->activeConcessionPolicyMap($student->id);
            $placement = [
                'branch_id' => (int) ($source['branch_id'] ?? 0),
                'class_id' => (int) ($source['class_id'] ?? 0),
                'session_id' => (int) ($source['session_id'] ?? 0),
                'section_id' => (int) ($source['section_id'] ?? 0),
            ];
            $snapshot['existing_fee_structure'] = $this->existingFeeStructurePreview(
                $student,
                $placement,
                $policyHeads
            );
            $snapshot['existing_selected_head_ids'] = $selected;
            $snapshot['current_structure_updated_at'] = now()->toISOString();
            $snapshot['current_structure_updated_by'] = [
                'id' => \Auth::id(),
                'name' => \Auth::user()->name,
            ];
        });

        $record->snapshot = json_encode($snapshot);
        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Current student fee structure selection updated successfully.',
            'existing_fee_structure' => $snapshot['existing_fee_structure'] ?? [],
        ]);
    }

    public function challanNo()
    {
        $latest = Challans::where('created_by', '=', \Auth::user()->creatorId())
            ->orderBY('id', 'desc')
            ->first();

        return $latest ? $latest->challanNo + 1 : 1;
    }

    public function edit(StudentReadmission $StudentReadmission, $id)
    {
        $StudentReadmission = StudentReadmission::with('challan')->findOrFail($id);

        // For new approval-based applications, company should review the immutable
        // snapshot instead of editing live values.
        if (\Auth::user()->type === 'company' && !empty($StudentReadmission->snapshot)) {
            $record = $StudentReadmission;
            $snapshot = $this->decodeJsonColumn($record->snapshot);
            $editable = strtolower(trim((string) $record->status)) === 'for approval';
            $generatedChallans = $this->generatedReadmissionChallans($record);

            $reviewBranches = User::where('type', 'branch')
                ->where('created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $reviewBranches->prepend(\Auth::user()->name, \Auth::user()->id);

            $reviewSessions = Session::where('created_by', \Auth::user()->creatorId())
                ->orderByDesc('id')
                ->get()
                ->pluck('year', 'id');

            $target = $snapshot['target_placement'] ?? [];
            $targetBranchId = (int) ($target['branch_id'] ?? $record->branch_id);
            $targetClassId = (int) ($target['class_id'] ?? $record->class_id);
            $targetSectionId = (int) ($target['section_id'] ?? $record->target_section_id ?? 0);

            $reviewClasses = Classes::where('owned_by', $targetBranchId)
                ->where('created_by', \Auth::user()->creatorId())
                ->where('active_status', 1)
                ->get()
                ->pluck('name', 'id');

            $reviewSections = $targetSectionId
                ? Section::where('id', $targetSectionId)->pluck('name', 'id')
                : collect();

            return view(
                'students.student_readmission.review',
                compact(
                    'record',
                    'snapshot',
                    'editable',
                    'reviewBranches',
                    'reviewSessions',
                    'reviewClasses',
                    'reviewSections',
                    'generatedChallans'
                )
            );
        }

        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')
                ->where('created_by', '=', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        }

        $class = Classes::where('id', '=', $StudentReadmission->class_id)->pluck('name', 'id');
        $session = Session::where('id', '=', $StudentReadmission->session_id)->pluck('year', 'id');
        $std = StudentRegistration::select(
            \DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'),
            'roll_no'
        )
            ->where('roll_no', $StudentReadmission->student_id)
            ->pluck('stdname', 'roll_no');

        return view(
            'students.student_readmission.edit',
            compact('branches', 'StudentReadmission', 'class', 'std', 'session')
        );
    }

    public function update(Request $request, StudentReadmission $StudentReadmission, $id)
    {
        $validator = \Validator::make($request->all(), [
            'student_id' => 'required',
            'readmission_date' => 'required|date',
            'branch_id' => 'required',
            'class_id' => 'required',
            'session_id' => 'required',
            'month_date' => 'required',
            'issue_date' => 'date',
            'due_date' => 'date',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        DB::beginTransaction();

        try {
            $readmission = StudentReadmission::with('challan')->findOrFail($id);

            // Snapshot-based approval applications must not be edited after submission.
            if (!empty($readmission->snapshot) && strtolower((string) $readmission->status) === 'for approval') {
                DB::rollBack();

                return redirect()->back()->with(
                    'error',
                    'This application is already snapshotted for approval and cannot be edited. Reject it and create a new application if values must change.'
                );
            }

            $readmission->readmission_date = $request->readmission_date;
            $readmission->branch_id = $request->branch_id;
            $readmission->class_id = $request->class_id;
            $readmission->remarks = $request->reason;
            $readmission->session_id = $request->session_id;
            $readmission->save();

            if (!empty($readmission->challan_id)) {
                $challan = Challans::where('id', $readmission->challan_id)->first();
                if ($challan) {
                    $challan->challan_date = $request->readmission_date;
                    $challan->fee_month = $this->readmissionBillingMonth($request->month_date);
                    $challan->issue_date = $request->issue_date;
                    $challan->due_date = $request->due_date;
                    $challan->save();
                }
            }

            DB::commit();

            return redirect()->route('readmissionstudent.index')
                ->with('success', 'Student Re-Admission has been updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(StudentTransfer $studentTransfer)
    {
        return redirect()->route('readmissionstudent.index');
    }

    public function approve($id)
    {
        return $this->changeStatus($id, 'approved');
    }

    public function reject(Request $request, $id)
    {
        if (\Auth::user()->type !== 'company') {
            return redirect()->back()
                ->with('error', 'Only company users can reject readmission or re-enrollment applications.');
        }

        $validator = \Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', $validator->getMessageBag()->first());
        }

        $record = StudentReadmission::findOrFail($id);

        // Tenant protection for company review actions.
        if ((int) $record->created_by !== (int) \Auth::user()->creatorId()) {
            abort(403);
        }

        $currentStatus = strtolower(trim((string) $record->status));
        if ($currentStatus !== 'for approval') {
            return redirect()->back()
                ->with('error', 'Only applications currently For Approval can be rejected.');
        }

        $approvalSnapshot = $this->decodeJsonColumn($record->approval_snapshot);
        $approvalSnapshot['decision'] = 'rejected';
        $approvalSnapshot['rejected_at'] = now()->toISOString();
        $approvalSnapshot['rejected_by'] = [
            'id' => \Auth::id(),
            'name' => \Auth::user()->name,
        ];
        $approvalSnapshot['rejection_reason'] = $request->rejection_reason;

        $record->status = 'rejected';
        $record->approval_snapshot = json_encode($approvalSnapshot);
        $record->save();

        return redirect()->route('readmissionstudent.index')
            ->with('success', 'Application has been rejected successfully.');
    }
    public function changeStatus($id, $status)
    {
        $record = StudentReadmission::findOrFail($id);
        $normalizedStatus = strtolower(trim((string) $status));

        if (\Auth::user()->type == 'company') {
            if ((int) $record->created_by !== (int) \Auth::user()->creatorId()) {
                abort(403);
            }
        } elseif ((int) $record->owned_by !== (int) \Auth::user()->ownedId()) {
            abort(403);
        }

        if ($normalizedStatus === 'approved') {
            if (\Auth::user()->type !== 'company') {
                return redirect()->back()->with(
                    'error',
                    'Only company users can approve readmission or re-enrollment applications.'
                );
            }

            $currentStatus = strtolower(trim((string) $record->status));

            if ($currentStatus === 'approved') {
                return redirect()->route('readmissionstudent.index')
                    ->with('success', 'This application is already approved.');
            }

            if (!empty($record->snapshot) && $currentStatus !== 'for approval') {
                return redirect()->back()
                    ->with('error', 'Only applications currently For Approval can be approved.');
            }

            $snapshot = $this->decodeJsonColumn($record->snapshot);

            if (empty($snapshot)) {
                $this->approveLegacyReadmission($record);

                return redirect()->route('readmissionstudent.index')
                    ->with('success', 'Student Re-Admission has been approved successfully.');
            }

            $flowType = $snapshot['flow']['type'] ?? $record->flow_type;

            if (!in_array($flowType, ['readmission', 're_enrollment'], true)) {
                return redirect()->back()->with(
                    'error',
                    'Only Readmission and Re-enrollment applications require this approval action.'
                );
            }

            try {
                DB::transaction(function () use ($record, $snapshot, $flowType) {
                    $studentRegId =
                        $snapshot['student']['reg_id']
                        ?? null;

                    $student = StudentRegistration::findOrFail(
                        $studentRegId
                    );

                    $target =
                        $snapshot['target_placement']
                        ?? [];

                    $source =
                        $snapshot['source_placement']
                        ?? [];

                    $selectedHeadIds = collect(
                        $snapshot['selected_head_ids']
                        ?? []
                    )
                        ->map(fn($id) => (int) $id)
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    $selectedHeadIds =
                        $this->mergeLockedReadmissionHeadIds(
                            $snapshot['target_fee_structure']
                                ?? [],
                            $selectedHeadIds,
                            $flowType
                        );

                    if (empty($selectedHeadIds)) {
                        throw new \RuntimeException(
                            'The snapshot contains no chargeable selected fee heads.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | HO APPROVAL: GENERATE MAIN CHALLAN ONLY
                    |--------------------------------------------------------------------------
                    |
                    | Do NOT yet:
                    | - readmit/re-enroll student;
                    | - update StudentRegistration / StudentEnrollments;
                    | - update StudentFeeStructure;
                    | - close withdrawal;
                    | - write promotion/history/fee revision records.
                    |
                    | Those actions run on the FIRST positive receipt of this
                    | application main challan.
                    |
                    */
                    $generatedChallanIds = [];

                    $mainChallan =
                        $this->createAdmissionChallanFromSnapshot(
                            $student,
                            $target,
                            $snapshot['request'] ?? [],
                            $snapshot['target_fee_structure']
                                ?? [],
                            $selectedHeadIds,
                            $snapshot['active_policy'] ?? [],
                            $flowType
                        );

                    $record->challan_id =
                        $mainChallan->id;

                    $generatedChallanIds[] =
                        $mainChallan->id;

                    /*
                    |--------------------------------------------------------------------------
                    | Approval-time missing Regular challans
                    |--------------------------------------------------------------------------
                    |
                    | These challans are generated from the saved snapshot only.
                    | StudentFeeStructure is NOT mutated to generate them.
                    |
                    */
                    $selectedGapMonths =
                        $snapshot['selected_gap_months']
                        ?? [];

                    if (
                        empty($selectedGapMonths)
                        && !empty(
                            $snapshot['generate_gap_months']
                        )
                    ) {
                        $selectedGapMonths =
                            $snapshot['gap_months']
                            ?? [];
                    }

                    $readmissionBillingMonth =
                        Carbon::parse(
                            $snapshot['request']['billing_month']
                            ?? $snapshot['request']['readmission_date']
                            ?? date('Y-m-d')
                        )
                            ->startOfMonth()
                            ->toDateString();

                    $selectedGapMonths = collect(
                        $selectedGapMonths
                    )
                        ->map(function ($month) {
                            return Carbon::parse(
                                $month
                            )
                                ->startOfMonth()
                                ->toDateString();
                        })
                        ->when(
                            $flowType === 'readmission',
                            function ($months) use (
                                $readmissionBillingMonth
                            ) {
                                /*
                                 * Same month Tuition is already on
                                 * Re-Admission challan.
                                 */
                                return $months->reject(
                                    fn($month) =>
                                        $month
                                        === $readmissionBillingMonth
                                );
                            }
                        )
                        ->unique()
                        ->values()
                        ->all();

                    foreach (
                        $selectedGapMonths
                        as $monthDate
                    ) {
                        if ($flowType === 'readmission') {
                            $regularRows =
                                $this->readmissionApprovalRegularRows(
                                    $snapshot,
                                    $monthDate
                                );

                            $effectiveMonth = Carbon::parse(
                                $snapshot['fee_revision']['effective_from']
                                ?? $readmissionBillingMonth
                            )->startOfMonth();

                            $regularPlacement =
                                Carbon::parse($monthDate)
                                    ->startOfMonth()
                                    ->lt($effectiveMonth)
                                    ? $source
                                    : $target;
                        } else {
                            $regularRows =
                                $this->reEnrollmentApprovalRegularRows(
                                    $snapshot,
                                    $selectedHeadIds
                                );

                            $regularPlacement = $target;
                        }

                        if (empty($regularRows)) {
                            throw new \RuntimeException(
                                'No approved snapshot fee rows are available for Regular challan '
                                . Carbon::parse(
                                    $monthDate
                                )->format('F Y')
                                . '.'
                            );
                        }

                        $regular =
                            $this->generateRegularChallanFromSnapshot(
                                $student,
                                $regularPlacement,
                                $monthDate,
                                $regularRows,
                                $snapshot['active_policy']
                                    ?? [],
                                true
                            );

                        if ($regular) {
                            $generatedChallanIds[] =
                                $regular->id;
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | READMISSION: previous unpaid Late Fee update
                    |--------------------------------------------------------------------------
                    |
                    | Same rule as ChallanController::bulkchallan():
                    | - older Issued challans only;
                    | - exclude all challans generated by this approval;
                    | - skip challans already containing Late Fee;
                    | - weekend-adjust due date;
                    | - Rs. 120/day, capped at Rs. 1,200;
                    | - add Late Fee head + Income/Receivable journal rows;
                    | - increment previous challan total_amount.
                    |
                    */
                    $lateFeeUpdatedChallanIds = [];

                    if ($flowType === 'readmission') {
                        $lateFeeUpdatedChallanIds =
                            $this->applyBulkLateFeeToPreviousUnpaidChallans(
                                $student->id,
                                $generatedChallanIds
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Approval audit = PENDING PAYMENT IMPLEMENTATION
                    |--------------------------------------------------------------------------
                    */
                    $approvalSnapshot =
                        $this->decodeJsonColumn(
                            $record->approval_snapshot
                        );

                    $approvalSnapshot['approved_at'] =
                        now()->toISOString();

                    $approvalSnapshot['approved_by'] = [
                        'id' => \Auth::id(),
                        'name' => \Auth::user()->name,
                    ];

                    $approvalSnapshot['approved_target_placement'] =
                        $target;

                    $approvalSnapshot['selected_head_ids'] =
                        $selectedHeadIds;

                    $approvalSnapshot['fee_revision'] =
                        $snapshot['fee_revision']
                        ?? [];

                    $approvalSnapshot['generated_challan_ids'] =
                        array_values(
                            array_unique(
                                $generatedChallanIds
                            )
                        );

                    $approvalSnapshot['late_fee_updated_challan_ids'] =
                        array_values(
                            array_unique(
                                $lateFeeUpdatedChallanIds
                            )
                        );

                    $approvalSnapshot['implementation'] = [
                        'status' => 'pending_payment',
                        'trigger' => 'first_positive_receipt',
                        'main_challan_id' =>
                            (int) $mainChallan->id,
                        'main_challan_no' =>
                            $mainChallan->challanNo,
                        'main_challan_type' =>
                            $mainChallan->challan_type,
                        'message' =>
                            'Student placement, fee structure and history will be applied on the first positive receipt.',
                    ];

                    $record->status = 'approved';
                    $record->approved_by = \Auth::id();
                    $record->approved_at = now();

                    $record->approval_snapshot =
                        json_encode(
                            $approvalSnapshot
                        );

                    $record->challan_ids =
                        json_encode(
                            array_values(
                                array_unique(
                                    $generatedChallanIds
                                )
                            )
                        );

                    $record->save();
                });
            } catch (\Throwable $e) {
                return redirect()->back()
                    ->with(
                        'error',
                        $e->getMessage()
                    );
            }

            return redirect()->route('readmissionstudent.index')
                ->with(
                    'success',
                    $this->readmissionFlowLabel($flowType)
                    . ' approved and challan(s) generated. Student readmission/re-enrollment, placement, fee structure and history will be applied on the first positive receipt.'
                );
        }

        if (
            in_array(
                $normalizedStatus,
                ['rejected', 'rollbacked', 'cancelled', 'canceled'],
                true
            )
        ) {
            if (\Auth::user()->type !== 'company') {
                return redirect()->back()
                    ->with(
                        'error',
                        'Only company users can reject or cancel approval-based applications.'
                    );
            }

            if (
                !empty($record->snapshot)
                && strtolower(trim((string) $record->status)) !== 'for approval'
            ) {
                return redirect()->back()
                    ->with(
                        'error',
                        'Only applications currently For Approval can be rejected or canceled.'
                    );
            }

            $record->status = $normalizedStatus;
            $record->save();

            return redirect()->route('readmissionstudent.index')
                ->with('success', 'Application status changed successfully.');
        }

        $record->status = $normalizedStatus;
        $record->save();

        return redirect()->route('readmissionstudent.index')
            ->with('success', 'Application Status Changed Successfully.');
    }

    public function readmission(Request $request)
    {
        $query = StudentRegistration::with('enrollment')
            ->where('class_id', $request->class_id);

        if (!empty($request->branch_id)) {
            $query->where(function ($studentQuery) use ($request) {
                $studentQuery->where('owned_by', $request->branch_id)
                    ->orWhere('branch', $request->branch_id);
            });
        }

        $student = $query->get()
            ->filter(function ($student) {
                $withdrawal = StudentWithdrawal::where('student_id', $student->id)
                    ->orderBy('id', 'desc')
                    ->first();

                return $withdrawal
                    && in_array(
                        strtolower((string) $withdrawal->status),
                        ['draft', 'approved'],
                        true
                    );
            })
            ->values()
            ->map(function ($student) {
                return [
                    'reg_id' => $student->id,
                    'roll_no' => optional($student->enrollment)->enrollId ?? $student->roll_no,
                    'stdname' => $student->stdname,
                    'fathername' => $student->fathername,
                    'branch_id' => $student->owned_by,
                    'class_id' => $student->class_id,
                    'section_id' => $student->enrollment
                        ? $student->enrollment->section_id
                        : $student->section_id,
                    'session_id' => optional($student->enrollment)->session_id
                        ?? $student->session_id,
                ];
            });

        return response()->json(['student' => $student]);
    }

    public function preview(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'branch_id' => 'required',
            'class_id' => 'required',
            'session_id' => 'required',
            'student_id' => 'required',
            'month_date' => 'required',
            'tuition_increment_enabled' => 'nullable|boolean',
            'tuition_increment_percentage' => 'nullable|integer|min:0',
            'tuition_increment_effective_from' => 'nullable|date_format:Y-m',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->getMessageBag()->first(),
            ], 422);
        }

        $context = $this->resolveReadmissionContext($request->student_id);
        if (!$context) {
            return response()->json([
                'success' => false,
                'message' => 'Student readmission context could not be loaded. Please verify that a withdrawal record exists for this student.',
            ], 404);
        }

        $flowType = $this->readmissionFlowType(
            $context['withdrawal'],
            $request->input('flow_type')
        );

        if (!$flowType) {
            return response()->json([
                'success' => false,
                'message' => 'The selected withdrawal is not eligible for this scenario.',
            ], 422);
        }

        // Branch users are restricted to the 30-day Reactivation window.
        // Company users can manually override the scenario to Reactivation.
        if (
            \Auth::user()->type === 'branch'
            && $flowType === 'reactivation'
            && !$this->isWithinReactivationWindow($context['withdrawal'])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Reactivation is only allowed within 30 days of withdrawal.',
            ], 422);
        }

        $billingMonth = $this->readmissionBillingMonth($request->month_date);
        $sourcePlacement = $this->sourcePlacement(
            $context['student'],
            $context['enrollment']
        );

        $targetPlacement = [
            'branch_id' => (int) ($request->input('new_branch_id') ?: $sourcePlacement['branch_id']),
            'class_id' => (int) ($request->input('new_class_id') ?: $sourcePlacement['class_id']),
            'session_id' => (int) ($request->input('new_session_id') ?: $sourcePlacement['session_id']),
            'section_id' => (int) ($request->input('new_section_id') ?: $sourcePlacement['section_id']),
        ];

        list($policyHeads, $activeConcession) = $this->activeConcessionPolicyMap(
            $context['student']->id
        );

        $existingStructure = $this->existingFeeStructurePreview(
            $context['student'],
            $sourcePlacement,
            $policyHeads
        );

        $tuitionIncrementEnabled = $request->boolean(
            'tuition_increment_enabled',
            true
        );
        $tuitionIncrementPercentage = max(
            0,
            (int) $request->input('tuition_increment_percentage', 0)
        );

        $targetStructure = $flowType === 'reactivation'
            ? []
            : $this->targetFeeStructurePreview(
                $context['student'],
                $sourcePlacement,
                $targetPlacement,
                $flowType,
                $policyHeads,
                $tuitionIncrementPercentage,
                $tuitionIncrementEnabled
            );

        $feeRevision = $this->tuitionRevisionPreview(
            $context['student'],
            $sourcePlacement,
            $targetPlacement,
            $policyHeads,
            $tuitionIncrementPercentage,
            $tuitionIncrementEnabled
        );

        /*
         * User may choose when the Tuition increment becomes applicable.
         * Billing Month is only the default/fallback.
         */
        $tuitionIncrementEffectiveFrom =
            $this->tuitionIncrementEffectiveFrom(
                $request->input('tuition_increment_effective_from'),
                $billingMonth
            );

        $feeRevision['effective_from'] =
            $tuitionIncrementEffectiveFrom;

        $latestPolicy = $this->latestConcessionApplication($context['student']->id);
        $gapBilling = $this->gapBillingContext(
            $context['student']->id,
            $context['withdrawal']
        );

        $gapBilling = $this->applyReadmissionBillingMonthGapRule(
            $gapBilling,
            $flowType,
            $billingMonth
        );

        $unpaidChallans = $this->previousUnpaidChallans($context['student']->id);

        $response = [
            'success' => true,
            'flow_type' => $flowType,
            'flow_label' => $this->readmissionFlowLabel($flowType),
            'flow_rules' => [
                'reactivation_days' => 30,
                'readmission_months' => 3,
                'withdrawal_gap_days' => $this->withdrawalGapDays($context['withdrawal']),
            ],
            'approval_required' => $flowType !== 'reactivation',
            'message' => $this->flowPreviewMessage($flowType),
            'student_details' => $this->previewStudentDetails(
                $context['student'],
                $context['enrollment']
            ),
            'withdrawal_details' => $this->previewWithdrawalDetails(
                $context['withdrawal']
            ),
            'policy_details' => $this->previewPolicyDetails(
                $activeConcession,
                $policyHeads
            ),
            'latest_policy' => $this->previewLatestPolicyDetails($latestPolicy),
            'source_details' => $this->placementDetails($sourcePlacement),
            'destination_details' => $this->placementDetails($targetPlacement),
            'existing_fee_structure' => $existingStructure,
            'target_fee_structure' => $targetStructure,
            'fee_revision' => $feeRevision,
            // Backward compatibility for the existing Blade's old "heads" key.
            'heads' => $targetStructure,
            'gap_months' => $gapBilling['months'],
            'gap_month_statuses' => $gapBilling['month_statuses'],
            'gap_range' => $gapBilling['range'],
            'gap_anchor_challan' => $gapBilling['anchor'],
            'unpaid_challans' => $unpaidChallans,
            'billing_month' => $billingMonth,
            'generate_class_fee_url' => route(
                'student.fee_generate',
                $context['student']->id,
            ),
        ];

        if ($flowType !== 'reactivation' && empty($targetStructure)) {
            return response()->json([
                'success' => false,
                'message' => 'No applicable fee structure heads were found for the selected target branch, class, and session.',
            ], 422);
        }

        return response()->json($response);
    }

    public function policyStatus(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'student_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->getMessageBag()->first(),
            ], 422);
        }

        $context = $this->resolveReadmissionContext($request->student_id);
        if (!$context) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        list($policyHeads, $activeConcession) = $this->activeConcessionPolicyMap(
            $context['student']->id
        );

        return response()->json([
            'success' => true,
            'active_policy' => $this->previewPolicyDetails(
                $activeConcession,
                $policyHeads
            ),
            'latest_policy' => $this->previewLatestPolicyDetails(
                $this->latestConcessionApplication($context['student']->id)
            ),
        ]);
    }

    public function endPolicy(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'student_id' => 'required',
            'end_date' => 'required|date',
            'remarks' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->getMessageBag()->first(),
            ], 422);
        }

        $context = $this->resolveReadmissionContext($request->student_id);
        if (!$context) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        $concession = Concession::where('student_id', $context['student']->id)
            ->where('status', 'Approved')
            ->where('active_status', '!=', 0)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', date('Y-m-d'));
            })
            ->orderByDesc('id')
            ->first();

        if (!$concession) {
            return response()->json([
                'success' => false,
                'message' => 'No active policy was found to end.',
            ], 404);
        }

        $concession->end_date = Carbon::parse($request->end_date)->toDateString();
        $concession->cancel_date = Carbon::parse($request->end_date)->toDateString();
        $concession->cancel_remarks = $request->remarks;
        $concession->status = 'Canceled';
        $concession->active_status = 0;
        $concession->save();

        return response()->json([
            'success' => true,
            'message' => 'Existing policy has been ended.',
        ]);
    }

    private function resolveReadmissionContext($studentIdentifier)
    {
        /*
        |--------------------------------------------------------------------------
        | StudentRegistration Is The Primary Readmission Identity
        |--------------------------------------------------------------------------
        |
        | Branch selection sends StudentRegistration.id.
        |
        | A withdrawn student may no longer have a usable/current
        | StudentEnrollments row. That must NOT block Branch from preparing
        | a Readmission / Re-enrollment application for company approval.
        |
        */
        $student = StudentRegistration::with(
            'class',
            'session',
            'branches',
            'section',
            'enrollment'
        )->find($studentIdentifier);

        $enrollment = null;

        /*
         * Backward compatibility:
         * older callers may still pass enrollId instead of registration id.
         */
        if (!$student) {
            $enrollment = StudentEnrollments::where(
                'enrollId',
                $studentIdentifier
            )
                ->orderByDesc('id')
                ->first();

            if ($enrollment) {
                $student = StudentRegistration::with(
                    'class',
                    'session',
                    'branches',
                    'section',
                    'enrollment'
                )->find($enrollment->regId);
            }
        }

        if (!$student) {
            return null;
        }

        /*
         * Enrollment is OPTIONAL for application creation/preview.
         */
        if (!$enrollment) {
            $enrollment = StudentEnrollments::where(
                'regId',
                $student->id
            )
                ->orderByDesc('id')
                ->first();
        }

        /*
         * Withdrawal is required because the scenario is calculated from
         * the withdrawal date. Branch student selection already lists
         * withdrawn students; do not reject merely because enrollment is absent.
         */
        $withdrawal = StudentWithdrawal::with(
            'branch',
            'class',
            'session'
        )
            ->where(
                'student_id',
                $student->id
            )
            ->orderByDesc('id')
            ->first();

        if (!$withdrawal) {
            return null;
        }

        return [
            'student' => $student,
            'enrollment' => $enrollment,
            'withdrawal' => $withdrawal,
        ];
    }

    /**
     * AUTO rule:
     * 0-30 days => Reactivation
     * >30 days and <=3 months => Readmission
     * >3 months => Re-enrollment
     *
     * Manual Reactivation:
     * - Branch user: must still be within 30 days.
     * - Company user: may override and select Reactivation outside 30 days.
     */
    private function readmissionFlowType($withdrawal, $requestedFlowType = null)
    {
        $requestedFlowType = strtolower(trim((string) $requestedFlowType));

        if (in_array($requestedFlowType, ['reactivation', 'readmission', 're_enrollment'], true)) {
            if (
                $requestedFlowType === 'reactivation'
                && \Auth::user()->type === 'branch'
                && !$this->isWithinReactivationWindow($withdrawal)
            ) {
                return null;
            }

            return $requestedFlowType;
        }

        $status = strtolower((string) $withdrawal->status);
        if (!in_array($status, ['draft', 'approved'], true)) {
            return null;
        }

        $withdrawalDate = $this->withdrawalDate($withdrawal);
        if (!$withdrawalDate) {
            return 'readmission';
        }

        if ($withdrawalDate->copy()->addDays(30)->endOfDay()->gte(now())) {
            return 'reactivation';
        }

        if ($withdrawalDate->copy()->addMonthsNoOverflow(3)->endOfDay()->gte(now())) {
            return 'readmission';
        }

        return 're_enrollment';
    }

    private function readmissionFlowLabel($flowType)
    {
        if ($flowType === 'reactivation') {
            return 'Reactivation';
        }

        if ($flowType === 're_enrollment') {
            return 'Re-enrollment';
        }

        return 'Readmission';
    }
    private function flowPreviewMessage($flowType)
    {
        if ($flowType === 'reactivation') {
            return 'Reactivation detected within 30 days. No approval is required. Missing Regular challans use the student current checked StudentFeeStructure.';
        }

        if ($flowType === 'readmission') {
            return 'Readmission detected within the 3-month window. Re-Admission charges use the student existing fee amounts for recurring heads such as Tuition. Admission Fee and Security Fee are not chargeable on the Re-Admission challan. Company approval is required.';
        }

        return 'Re-enrollment detected after the 3-month window. Target class/session fees are shown for approval. When the session changes, Tuition can be revised from the student existing Tuition amount by the entered whole-number percentage.';
    }

    private function withdrawalDate($withdrawal)
    {
        $date = $withdrawal->withdraw_date ?: $withdrawal->apply_date;

        return $date ? Carbon::parse($date)->startOfDay() : null;
    }

    private function withdrawalGapDays($withdrawal)
    {
        $date = $this->withdrawalDate($withdrawal);

        return $date ? $date->diffInDays(Carbon::today()) : null;
    }

    private function isWithinReactivationWindow($withdrawal)
    {
        $date = $this->withdrawalDate($withdrawal);

        return $date
            ? $date->copy()->addDays(30)->endOfDay()->gte(now())
            : false;
    }

    private function readmissionBillingMonth($monthDate)
    {
        return Carbon::parse($monthDate)->startOfMonth()->toDateString();
    }

    private function sourcePlacement($student, $enrollment = null)
    {
        return [
            'branch_id' => (int) (
                optional($enrollment)->owned_by
                ?: $student->owned_by
                ?: $student->branch
            ),
            'class_id' => (int) (
                optional($enrollment)->class_id
                ?: $student->class_id
            ),
            'section_id' => (int) (
                optional($enrollment)->section_id
                ?: $student->section_id
            ),
            'session_id' => (int) (
                optional($enrollment)->session_id
                ?: $student->session_id
            ),
        ];
    }

    private function placementDetails(array $placement)
    {
        $branchId = (int) ($placement['branch_id'] ?? 0);
        $classId = (int) ($placement['class_id'] ?? 0);
        $sessionId = (int) ($placement['session_id'] ?? 0);
        $sectionId = (int) ($placement['section_id'] ?? 0);

        return [
            'branch_id' => $branchId,
            'class_id' => $classId,
            'session_id' => $sessionId,
            'section_id' => $sectionId,
            'branch' => optional(User::find($branchId))->name,
            'class' => optional(Classes::find($classId))->name,
            'session' => optional(Session::find($sessionId))->year,
            'section' => optional(Section::find($sectionId))->name,
        ];
    }

    private function activeConcessionPolicyMap($studentId)
    {
        $concession = Concession::with('concession')
            ->where('student_id', $studentId)
            ->where('status', 'Approved')
            ->where('active_status', '!=', 0)
            ->where(function ($query) {
                $query->where('end_date', '>=', date('Y-m-d'))
                    ->orWhereNull('end_date');
            })
            ->orderBy('id', 'desc')
            ->first();

        if (!$concession) {
            return [collect(), null];
        }

        return [
            ConcessionPolicyHead::where('concession_id', $concession->concession_id)
                ->pluck('percentage', 'head_id'),
            $concession,
        ];
    }

    private function latestConcessionApplication($studentId)
    {
        return Concession::with('concession')
            ->where('student_id', $studentId)
            ->orderByDesc('id')
            ->first();
    }

    private function previewStudentDetails($student, $enrollment = null)
    {
        $enrollmentId = optional($enrollment)->enrollId;
        $enrollmentOwnedBy = optional($enrollment)->owned_by;
        $enrollmentClassId = optional($enrollment)->class_id;
        $enrollmentSectionId = optional($enrollment)->section_id;
        $enrollmentSessionId = optional($enrollment)->session_id;

        return [
            'reg_id' => $student->id,
            'roll_no' => $enrollmentId ?: $student->roll_no,
            'student_name' => $student->stdname,
            'father_name' => $student->fathername,
            'branch' => optional($student->branches)->name,
            'class' => optional($student->class)->name,
            'section' => optional(Section::find(
                $enrollmentSectionId ?: $student->section_id
            ))->name,
            'session' => optional(Session::find(
                $enrollmentSessionId ?: $student->session_id
            ))->year,
            'student_status' => $student->student_status,
            'date_of_admission' => $this->studentAdmissionDate($student),
            'branch_id' => (int) (
                $enrollmentOwnedBy
                ?: $student->owned_by
                ?: $student->branch
            ),
            'class_id' => (int) (
                $enrollmentClassId
                ?: $student->class_id
            ),
            'session_id' => (int) (
                $enrollmentSessionId
                ?: $student->session_id
            ),
            'section_id' => (int) (
                $enrollmentSectionId
                ?: $student->section_id
            ),
        ];
    }

    private function studentAdmissionDate($student)
    {
        foreach (['admission_date', 'date_of_admission', 'admissionDate', 'doa'] as $attribute) {
            $value = $student->{$attribute} ?? null;
            if (!empty($value)) {
                try {
                    return Carbon::parse($value)->toDateString();
                } catch (\Throwable $e) {
                    return $value;
                }
            }
        }

        return $student->created_at
            ? Carbon::parse($student->created_at)->toDateString()
            : null;
    }

    private function previewWithdrawalDetails($withdrawal)
    {
        return [
            'id' => $withdrawal->id,
            'status' => ucfirst((string) $withdrawal->status),
            'withdraw_date' => $withdrawal->withdraw_date,
            'apply_date' => $withdrawal->apply_date,
            'expected_readmission_date' => $withdrawal->expected_readmission_date,
            'reason' => $withdrawal->reason,
            'remark' => $withdrawal->remark,
            'branch_id' => $withdrawal->branch_id,
            'class_id' => $withdrawal->class_id,
            'session_id' => $withdrawal->session_id,
            'branch' => optional($withdrawal->branch)->name,
            'class' => optional($withdrawal->class)->name,
            'session' => optional($withdrawal->session)->year,
        ];
    }

    private function previewPolicyDetails($concession, $policyHeads)
    {
        if (!$concession) {
            return [
                'has_policy' => false,
                'concession_record_id' => null,
                'concession_id' => null,
                'policy_title' => null,
                'status' => null,
                'active_status' => null,
                'apply_date' => null,
                'start_date' => null,
                'end_date' => null,
                'remarks' => null,
                'heads' => [],
            ];
        }

        $concession->loadMissing('concession');

        $headDetails = collect($policyHeads)
            ->map(function ($percentage, $headId) {
                $feeHead = FeeHead::find($headId);

                return [
                    'head_id' => (int) $headId,
                    'fee_head' => optional($feeHead)->fee_head,
                    'percentage' => (float) $percentage,
                ];
            })
            ->values()
            ->all();

        return [
            'has_policy' => true,
            'concession_record_id' => $concession->id,
            'concession_id' => $concession->concession_id,
            'policy_title' => optional($concession->concession)->title,
            'status' => $concession->status,
            'active_status' => $concession->active_status,
            'apply_date' => $concession->apply_date,
            'start_date' => $concession->start_date,
            'end_date' => $concession->end_date,
            'remarks' => $concession->remarks,
            'heads' => $headDetails,
        ];
    }

    private function previewLatestPolicyDetails($concession)
    {
        if (!$concession) {
            return [
                'exists' => false,
            ];
        }

        $concession->loadMissing('concession');

        return [
            'exists' => true,
            'id' => $concession->id,
            'concession_id' => $concession->concession_id,
            'policy_title' => optional($concession->concession)->title,
            'status' => $concession->status,
            'active_status' => $concession->active_status,
            'apply_date' => $concession->apply_date,
            'start_date' => $concession->start_date,
            'end_date' => $concession->end_date,
            'remarks' => $concession->remarks,
        ];
    }

    private function existingFeeStructurePreview($student, array $placement, $policyHeads)
    {
        $rows = StudentFeeStructure::with('feehead')
            ->where('reg_id', $student->id)
            // ->where('class_id', $placement['class_id'])
            // ->where(function ($query) use ($placement) {
            //     $query->where('branch_id', $placement['branch_id'])
            //         ->orWhere('owned_by', $placement['branch_id']);
            // })
            // ->where('checked_status', 1)
            ->get();
        // dd($placement,$rows);

        if ($rows->isEmpty()) {
            return $this->classWiseFeeStructurePreview(
                $student,
                $placement,
                $policyHeads
            );
        }

        return $rows->map(function ($row) use ($policyHeads) {
            $amount = (float) ($row->amount ?? 0);
            $baseDiscount = (float) ($row->discount ?? 0);
            $effectiveDiscount = $policyHeads->has($row->head_id)
                ? (float) $policyHeads->get($row->head_id)
                : $baseDiscount;

            return [
                'head_id' => (int) $row->head_id,
                'fee_head' => optional($row->feehead)->fee_head ?: ('Head #' . $row->head_id),
                'class_amount' => round($amount, 2),
                'base_discount' => round($baseDiscount, 2),
                'discount' => round($effectiveDiscount, 2),
                'payable_amount' => round(
                    $amount - (($amount * $effectiveDiscount) / 100),
                    2
                ),
                'checked' => (int) ($row->checked_status ?? 0),
                'source' => 'student_fee_structure',
            ];
        })->values()->all();
    }

    private function normalizeFeeHeadName($name)
    {
        return strtolower(
            preg_replace('/\s+/', ' ', trim((string) $name))
        );
    }

    private function isTuitionFeeHeadName($name)
    {
        return str_contains(
            $this->normalizeFeeHeadName($name),
            'tuition'
        );
    }

    private function isReadmissionFeeHeadName($name)
    {
        $name = $this->normalizeFeeHeadName($name);

        return str_contains($name, 'readmission')
            || str_contains($name, 're-admission')
            || str_contains($name, 're admission');
    }

    private function isAdmissionOrSecurityHeadForReadmission($name)
    {
        $name = $this->normalizeFeeHeadName($name);

        if ($this->isReadmissionFeeHeadName($name)) {
            return false;
        }

        return str_contains($name, 'security')
            || str_contains($name, 'admission');
    }

    private function tuitionRevisionPreview(
        $student,
        array $sourcePlacement,
        array $targetPlacement,
        $policyHeads,
        $percentage = 0,
        $enabled = true
    ) {
        $sessionChanged = (int) ($sourcePlacement['session_id'] ?? 0)
            !== (int) ($targetPlacement['session_id'] ?? 0);

        $percentage = max(0, (int) $percentage);
        $enabled = (bool) $enabled;

        $existingRows = collect(
            $this->existingFeeStructurePreview(
                $student,
                $sourcePlacement,
                $policyHeads
            )
        );

        $tuition = $existingRows->first(function ($row) {
            return $this->isTuitionFeeHeadName(
                (string) ($row['fee_head'] ?? '')
            );
        });

        if (!$tuition) {
            return [
                'session_changed' => $sessionChanged,
                'enabled' => $enabled,
                'percentage' => $percentage,
                'head_id' => null,
                'fee_head' => 'Tuition Fee',
                'prev_base_amount' => 0,
                'new_base_amount' => 0,
                'base_discount' => 0,
                'discount' => 0,
                'prev_payable_amount' => 0,
                'new_payable_amount' => 0,
            ];
        }

        $prevBase = (float) ($tuition['class_amount'] ?? 0);
        $baseDiscount = (float) ($tuition['base_discount'] ?? 0);
        $discount = (float) ($tuition['discount'] ?? 0);

        $appliedPercentage = ($sessionChanged && $enabled)
            ? $percentage
            : 0;

        $newBase = round(
            $prevBase + (($prevBase * $appliedPercentage) / 100)
        );

        return [
            'session_changed' => $sessionChanged,
            'enabled' => $enabled,
            'percentage' => $appliedPercentage,
            'head_id' => (int) ($tuition['head_id'] ?? 0),
            'fee_head' => $tuition['fee_head'] ?? 'Tuition Fee',
            'prev_base_amount' => round($prevBase, 2),
            'new_base_amount' => round($newBase, 2),
            'base_discount' => round($baseDiscount, 2),
            'discount' => round($discount, 2),
            'prev_payable_amount' => round(
                $prevBase - (($prevBase * $discount) / 100),
                2
            ),
            'new_payable_amount' => round(
                $newBase - (($newBase * $discount) / 100),
                2
            ),
        ];
    }

    private function readmissionChargeStructurePreview(
        $student,
        array $sourcePlacement,
        array $targetPlacement,
        $policyHeads,
        $tuitionIncrementPercentage = 0,
        $tuitionIncrementEnabled = true
    ) {
        $existingRows = collect(
            $this->existingFeeStructurePreview(
                $student,
                $sourcePlacement,
                $policyHeads
            )
        );

        $classRows = collect(
            $this->classWiseFeeStructurePreview(
                $student,
                $targetPlacement,
                $policyHeads
            )
        );

        $existingByHead = $existingRows->keyBy(
            fn($row) => (int) ($row['head_id'] ?? 0)
        );

        $classByHead = $classRows->keyBy(
            fn($row) => (int) ($row['head_id'] ?? 0)
        );

        $revision = $this->tuitionRevisionPreview(
            $student,
            $sourcePlacement,
            $targetPlacement,
            $policyHeads,
            $tuitionIncrementPercentage,
            $tuitionIncrementEnabled
        );

        $headIds = $classRows->pluck('head_id')
            ->merge($existingRows->pluck('head_id'))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $result = [];

        foreach ($headIds as $headId) {
            $existing = $existingByHead->get($headId);
            $classRow = $classByHead->get($headId);

            $name = (string) (
                $existing['fee_head']
                ?? $classRow['fee_head']
                ?? ('Head #' . $headId)
            );

            $isForbidden = $this->isAdmissionOrSecurityHeadForReadmission($name);
            $isReadmissionFee = $this->isReadmissionFeeHeadName($name);
            $isTuition = $this->isTuitionFeeHeadName($name);

            if ($existing) {
                $row = $existing;

                $existingAmount = (float) ($existing['class_amount'] ?? 0);
                $chargeAmount = $existingAmount;

                if (
                    $isTuition
                    && !empty($revision['session_changed'])
                    && !empty($revision['enabled'])
                    && (int) ($revision['head_id'] ?? 0) === (int) $headId
                ) {
                    $chargeAmount = (float) ($revision['new_base_amount'] ?? $existingAmount);
                }

                $discount = (float) ($existing['discount'] ?? 0);

                $row['existing_amount'] = round($existingAmount, 2);
                $row['class_reference_amount'] = $classRow
                    ? round((float) ($classRow['class_amount'] ?? 0), 2)
                    : null;
                $row['class_amount'] = round($chargeAmount, 2);
                $row['charge_amount'] = round($chargeAmount, 2);
                $row['payable_amount'] = round(
                    $chargeAmount - (($chargeAmount * $discount) / 100),
                    2
                );
                $row['increment_percentage'] = $isTuition
                    ? (int) ($revision['percentage'] ?? 0)
                    : 0;
                $row['source'] = 'student_fee_structure';
                $row['charge_source'] = $isTuition && (int) ($revision['percentage'] ?? 0) > 0
                    ? 'student_fee_structure_revision'
                    : 'student_fee_structure';
                $row['charge_source_label'] = $isTuition && !empty($revision['session_changed'])
                    ? 'Existing Student Fee' . ((int) ($revision['percentage'] ?? 0) > 0 ? ' + Increment' : '')
                    : 'Existing Student Fee';
            } else {
                $row = $classRow ?: [];

                $classAmount = (float) ($row['class_amount'] ?? 0);

                $row['existing_amount'] = null;
                $row['class_reference_amount'] = round($classAmount, 2);
                $row['charge_amount'] = round($classAmount, 2);
                $row['charge_source'] = 'class_wise_fee';
                $row['charge_source_label'] = 'Class Fee Structure';
                $row['increment_percentage'] = 0;
            }

            /*
             * Readmission defaults:
             *
             * - RE-ADMISSION FEE is automatically selected.
             * - Tuition is ALWAYS included on the Re-Admission fee month.
             *   The user does not need to select it manually.
             * - Admission Fee / Security Fee remain disabled + unchecked.
             *
             * Tuition still uses the student's existing Tuition amount
             * (plus the approved session increment when applicable).
             */
            $row['auto_charge'] = $isTuition ? 1 : 0;
            $row['auto_charge_reason'] = $isTuition
                ? 'Automatically charged on the Re-Admission Fee Month'
                : null;

            $row['checked'] = ($isReadmissionFee || $isTuition) ? 1 : 0;

            if ($isForbidden) {
                $row['checked'] = 0;
                $row['auto_charge'] = 0;
                $row['branch_disabled'] = 1;
                $row['disabled_reason'] = 'Not chargeable on a Re-Admission challan';
            } else {
                $row['branch_disabled'] = 0;
            }

            $result[] = $row;
        }

        return array_values($result);
    }

    private function targetFeeStructurePreview(
        $student,
        array $sourcePlacement,
        array $targetPlacement,
        $flowType,
        $policyHeads,
        $tuitionIncrementPercentage = 0,
        $tuitionIncrementEnabled = true
    ) {
        $tuitionIncrementPercentage = max(0, (int) $tuitionIncrementPercentage);
        $sessionChanged = (int) ($sourcePlacement['session_id'] ?? 0)
            !== (int) ($targetPlacement['session_id'] ?? 0);

        /*
        |--------------------------------------------------------------------------
        | READMISSION
        |--------------------------------------------------------------------------
        |
        | A Readmission challan does NOT replace the student's existing
        | recurring fee amounts with the target class fee amounts.
        |
        | Existing student amounts are the charge source for matching heads
        | (especially Tuition). Target class structure is used only to expose
        | applicable one-time heads such as Re-Admission Fee.
        |
        */
        if ($flowType === 'readmission') {
            return $this->readmissionChargeStructurePreview(
                $student,
                $sourcePlacement,
                $targetPlacement,
                $policyHeads,
                $sessionChanged ? $tuitionIncrementPercentage : 0,
                $sessionChanged && (bool) $tuitionIncrementEnabled
            );
        }

        $placementChanged = $sessionChanged
            || (int) ($sourcePlacement['class_id'] ?? 0) !== (int) ($targetPlacement['class_id'] ?? 0)
            || (int) ($sourcePlacement['branch_id'] ?? 0) !== (int) ($targetPlacement['branch_id'] ?? 0);

        if ($flowType === 're_enrollment' || $placementChanged) {
            $rows = $this->classWiseFeeStructurePreview(
                $student,
                $targetPlacement,
                $policyHeads
            );

            /*
             * Session change behaves like promotion for Tuition:
             * existing student Tuition is the revision base, then the entered
             * percentage is applied on that base. The class amount remains
             * available as a reference only.
             */
            if ($sessionChanged && $tuitionIncrementEnabled) {
                $revision = $this->tuitionRevisionPreview(
                    $student,
                    $sourcePlacement,
                    $targetPlacement,
                    $policyHeads,
                    $tuitionIncrementPercentage,
                    true
                );

                if (!empty($revision['head_id'])) {
                    $found = false;

                    foreach ($rows as &$row) {
                        if ((int) ($row['head_id'] ?? 0) !== (int) $revision['head_id']) {
                            continue;
                        }

                        $found = true;
                        $classReference = (float) ($row['class_amount'] ?? 0);
                        $row['class_reference_amount'] = round($classReference, 2);
                        $row['existing_amount'] = round((float) $revision['prev_base_amount'], 2);
                        $row['class_amount'] = round((float) $revision['new_base_amount'], 2);
                        $row['charge_amount'] = round((float) $revision['new_base_amount'], 2);
                        $row['discount'] = round((float) $revision['discount'], 2);
                        $row['payable_amount'] = round((float) $revision['new_payable_amount'], 2);
                        $row['increment_percentage'] = (int) $revision['percentage'];
                        $row['charge_source'] = 'student_fee_structure_revision';
                        $row['charge_source_label'] = 'Existing Student Fee + Increment';
                        break;
                    }
                    unset($row);

                    if (!$found) {
                        $rows[] = [
                            'head_id' => (int) $revision['head_id'],
                            'fee_head' => $revision['fee_head'] ?? 'Tuition Fee',
                            'class_amount' => round((float) $revision['new_base_amount'], 2),
                            'class_reference_amount' => null,
                            'existing_amount' => round((float) $revision['prev_base_amount'], 2),
                            'base_discount' => round((float) $revision['base_discount'], 2),
                            'discount' => round((float) $revision['discount'], 2),
                            'payable_amount' => round((float) $revision['new_payable_amount'], 2),
                            'charge_amount' => round((float) $revision['new_base_amount'], 2),
                            'increment_percentage' => (int) $revision['percentage'],
                            'checked' => 1,
                            'source' => 'student_fee_structure_revision',
                            'charge_source' => 'student_fee_structure_revision',
                            'charge_source_label' => 'Existing Student Fee + Increment',
                        ];
                    }
                }
            }

            return array_values($rows);
        }

        return $this->existingFeeStructurePreview(
            $student,
            $sourcePlacement,
            $policyHeads
        );
    }

    private function classWiseFeeStructurePreview($student, array $placement, $policyHeads)
    {
        $teacherChildOption = Registring_option::where('name', 'TEACHER CHILD')->first();
        $type = ($teacherChildOption
            && (int) $student->register_option === (int) $teacherChildOption->id)
            ? 'teacher_child'
            : 'regular';

        return ClassWiseFee::with('feehead')
            ->where('session_id', $placement['session_id'])
            ->where('class_id', $placement['class_id'])
            ->where('owned_by', $placement['branch_id'])
            ->where('type', $type)
            ->get()
            ->map(function ($row) use ($policyHeads) {
                $amount = (float) ($row->amount ?? 0);
                $baseDiscount = 0;
                $effectiveDiscount = $policyHeads->has($row->head_id)
                    ? (float) $policyHeads->get($row->head_id)
                    : 0;

                return [
                    'head_id' => (int) $row->head_id,
                    'fee_head' => optional($row->feehead)->fee_head ?: ('Head #' . $row->head_id),
                    'class_amount' => round($amount, 2),
                    'base_discount' => $baseDiscount,
                    'discount' => round($effectiveDiscount, 2),
                    'payable_amount' => round(
                        $amount - (($amount * $effectiveDiscount) / 100),
                        2
                    ),
                    'checked' => 1,
                    'source' => 'class_wise_fee',
                ];
            })
            ->values()
            ->all();
    }

    // Kept for compatibility with any existing internal calls.
    private function readmissionPreviewHeads($student, $sessionId, $classId, $branchId, $flowType)
    {
        $enrollment = StudentEnrollments::where('regId', $student->id)->first();
        $source = $this->sourcePlacement($student, $enrollment);
        $target = [
            'branch_id' => (int) $branchId,
            'class_id' => (int) $classId,
            'session_id' => (int) $sessionId,
            'section_id' => (int) optional($enrollment)->section_id,
        ];

        list($policyHeads, ) = $this->activeConcessionPolicyMap($student->id);

        return $this->targetFeeStructurePreview(
            $student,
            $source,
            $target,
            $flowType,
            $policyHeads
        );
    }

    private function lockedReadmissionHeadNames()
    {
        return ['ADMISSION FEE', 'SECURITY FEE'];
    }
    private function mergeLockedReadmissionHeadIds(
        array $previewHeads,
        array $selectedHeadIds,
        $flowType
    ) {
        $selectedHeadIds = collect($selectedHeadIds)
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | COMPANY / HO
        |--------------------------------------------------------------------------
        |
        | Company review is fully editable.
        | Do not force-add, force-remove or lock any fee head at backend level.
        | Whatever Company selected in review is exactly what approval uses.
        |
        */
        if (\Auth::user()->type !== 'branch') {
            return $selectedHeadIds->all();
        }

        /*
        |--------------------------------------------------------------------------
        | BRANCH RESTRICTIONS
        |--------------------------------------------------------------------------
        |
        | Admission Fee and Security Fee are never part of a Re-Admission
        | challan. At Branch they are shown disabled + unchecked, and this
        | backend filter protects the same rule even if a forged request sends
        | those IDs.
        |
        */
        if ($flowType === 'readmission') {
            $forbiddenIds = collect($previewHeads)
                ->filter(function ($row) {
                    return $this->isAdmissionOrSecurityHeadForReadmission(
                        (string) ($row['fee_head'] ?? '')
                    );
                })
                ->pluck('head_id')
                ->map(fn($id) => (int) $id)
                ->all();

            /*
             * Tuition is an automatic Re-Admission fee-month charge.
             * It must remain included even when the frontend/user did not
             * explicitly submit the Tuition checkbox.
             */
            $automaticTuitionIds = collect($previewHeads)
                ->filter(function ($row) {
                    return $this->isTuitionFeeHeadName(
                        (string) ($row['fee_head'] ?? '')
                    );
                })
                ->pluck('head_id')
                ->map(fn($id) => (int) $id)
                ->filter()
                ->all();

            return $selectedHeadIds
                ->reject(fn($id) => in_array((int) $id, $forbiddenIds, true))
                ->merge($automaticTuitionIds)
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        /*
         * Reactivation uses the student's checked current structure.
         */
        if ($flowType === 'reactivation') {
            return $selectedHeadIds->all();
        }

        /*
         * Preserve the previous Re-enrollment branch rule:
         * Admission + Security are mandatory for branch-created Admission.
         */
        $lockedHeadIds = collect($previewHeads)
            ->filter(function ($row) {
                return in_array(
                    strtoupper(trim((string) ($row['fee_head'] ?? ''))),
                    $this->lockedReadmissionHeadNames(),
                    true
                );
            })
            ->pluck('head_id')
            ->map(fn($headId) => (int) $headId)
            ->all();

        return $selectedHeadIds
            ->merge($lockedHeadIds)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Gap billing is based ONLY on the withdrawal month -> current month range.
     *
     * For every calendar month in that range:
     * - if an Advance challan covers the month, show it as Advance Covered;
     * - else if a Regular challan covers it, show it as Regular Covered;
     * - else if an Admission challan with Tuition covers it, show it as Admission Covered;
     * - otherwise mark it Missing.
     *
     * Only Missing months are returned in "months" and are eligible for generation.
     * Covered months remain visible in "month_statuses" for the branch/company review.
     */
    private function gapBillingContext($studentId, $withdrawal)
    {
        $withdrawalDate = $this->withdrawalDate($withdrawal);

        if (!$withdrawalDate) {
            return [
                'months' => [],
                'month_statuses' => [],
                'range' => [
                    'from' => null,
                    'to' => Carbon::today()->startOfMonth()->toDateString(),
                ],
                'anchor' => [
                    'exists' => false,
                ],
            ];
        }

        $cursor = $withdrawalDate->copy()->startOfMonth();
        $end = Carbon::today()->startOfMonth();

        $missingMonths = [];
        $monthStatuses = [];

        while ($cursor->lte($end)) {
            $monthDate = $cursor->toDateString();

            // Prefer Advance when the same month is covered by more than one challan.
            $coverageChallan = $this->regularOrAdvanceChallanForMonth(
                $studentId,
                $monthDate
            );

            if ($coverageChallan) {
                $type = strtolower(trim((string) $coverageChallan->challan_type));
                $coverageStatus = $type === 'advance'
                    ? 'advance_covered'
                    : 'regular_covered';

                $monthStatuses[] = [
                    'month' => $monthDate,
                    'status' => $coverageStatus,
                    'label' => $type === 'advance'
                        ? 'Advance challan exists'
                        : 'Regular challan exists',
                    'challan_id' => (int) $coverageChallan->id,
                    'challan_no' => $coverageChallan->challanNo,
                    'challan_type' => $coverageChallan->challan_type,
                    'challan_status' => $coverageChallan->status,
                ];

                $cursor->addMonthNoOverflow();
                continue;
            }

            if ($this->admissionWithTuitionExistsForMonth($studentId, $monthDate)) {
                $monthStatuses[] = [
                    'month' => $monthDate,
                    'status' => 'admission_covered',
                    'label' => 'Admission/Re-Admission challan with Tuition exists',
                    'challan_id' => null,
                    'challan_no' => null,
                    'challan_type' => 'Admission',
                    'challan_status' => null,
                ];

                $cursor->addMonthNoOverflow();
                continue;
            }

            $missingMonths[] = $monthDate;

            $monthStatuses[] = [
                'month' => $monthDate,
                'status' => 'missing',
                'label' => 'Missing - Regular challan will be generated if selected',
                'challan_id' => null,
                'challan_no' => null,
                'challan_type' => null,
                'challan_status' => null,
            ];

            $cursor->addMonthNoOverflow();
        }

        return [
            'months' => $missingMonths,
            'month_statuses' => $monthStatuses,
            'range' => [
                'from' => $withdrawalDate->copy()->startOfMonth()->toDateString(),
                'to' => $end->toDateString(),
            ],
            // Kept only for backward compatibility with any older view code.
            'anchor' => [
                'exists' => false,
            ],
        ];
    }

    /**
     * Resolve the user-selectable Tuition increment effective month.
     * HTML month inputs submit YYYY-MM. Billing Month is the fallback.
     */
    private function tuitionIncrementEffectiveFrom($value, $billingMonth)
    {
        if (!empty($value)) {
            try {
                return Carbon::createFromFormat('Y-m', (string) $value)
                    ->startOfMonth()
                    ->toDateString();
            } catch (\Throwable $e) {
                // Validation normally catches malformed values.
            }
        }

        return Carbon::parse($billingMonth)
            ->startOfMonth()
            ->toDateString();
    }

    /**
     * Readmission billing-month collision rule.
     *
     * If the Re-Admission fee month is also a missing Regular gap month,
     * that month belongs to the Re-Admission challan:
     *
     * - Tuition is automatically included on Re-Admission.
     * - No separate Regular challan is generated for the same month.
     * - The preview shows the month as covered by Readmission.
     *
     * Earlier missing months remain eligible for separate Regular billing.
     */
    private function applyReadmissionBillingMonthGapRule(
        array $gapBilling,
        $flowType,
        $billingMonth
    ) {
        if (strtolower((string) $flowType) !== 'readmission') {
            return $gapBilling;
        }

        try {
            $billingMonth = Carbon::parse(
                $billingMonth
            )->startOfMonth()->toDateString();
        } catch (\Throwable $e) {
            return $gapBilling;
        }

        $gapBilling['months'] = collect(
            $gapBilling['months'] ?? []
        )
            ->map(function ($month) {
                return Carbon::parse(
                    $month
                )->startOfMonth()->toDateString();
            })
            ->reject(function ($month) use ($billingMonth) {
                return $month === $billingMonth;
            })
            ->unique()
            ->values()
            ->all();

        $gapBilling['month_statuses'] = collect(
            $gapBilling['month_statuses'] ?? []
        )
            ->map(function ($row) use ($billingMonth) {
                $rowMonth = null;

                try {
                    $rowMonth = Carbon::parse(
                        $row['month'] ?? null
                    )->startOfMonth()->toDateString();
                } catch (\Throwable $e) {
                    return $row;
                }

                if (
                    $rowMonth === $billingMonth
                    && ($row['status'] ?? '') === 'missing'
                ) {
                    $row['status'] = 'readmission_covered';
                    $row['label'] =
                        'Covered by Re-Admission - Tuition is charged on the Re-Admission challan; no separate Regular challan';
                    $row['challan_type'] = 'Re-Admission';
                    $row['challan_id'] = null;
                    $row['challan_no'] = null;
                    $row['challan_status'] = null;
                }

                return $row;
            })
            ->values()
            ->all();

        return $gapBilling;
    }

    private function challanCoveredMonths($challan)
    {
        $months = collect();

        if (!empty($challan->fee_month)) {
            try {
                $months->push(
                    Carbon::parse($challan->fee_month)->startOfMonth()->toDateString()
                );
            } catch (\Throwable $e) {
                // Ignore malformed historical fee_month values.
            }
        }

        if (!empty($challan->other_months)) {
            foreach (explode(',', (string) $challan->other_months) as $monthValue) {
                $monthValue = trim($monthValue);

                if ($monthValue === '') {
                    continue;
                }

                try {
                    $months->push(
                        Carbon::parse($monthValue)->startOfMonth()->toDateString()
                    );
                } catch (\Throwable $e) {
                    // Ignore malformed historical other_months values.
                }
            }
        }

        return $months
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function previewGapAnchorChallan($challan, $lastCoveredMonth = null)
    {
        if (!$challan) {
            return [
                'exists' => false,
                'challan_id' => null,
                'challan_no' => null,
                'challan_type' => null,
                'fee_month' => null,
                'other_months' => null,
                'last_covered_month' => null,
                'status' => null,
            ];
        }

        return [
            'exists' => true,
            'challan_id' => (int) $challan->id,
            'challan_no' => $challan->challanNo,
            'challan_type' => $challan->challan_type,
            'fee_month' => $challan->fee_month,
            'other_months' => $challan->other_months,
            'last_covered_month' => $lastCoveredMonth
                ? $lastCoveredMonth->toDateString()
                : null,
            'status' => $challan->status,
        ];
    }

    /**
     * All historical challans that are not Paid.
     * Intentionally no challan_type filter: every outstanding challan is shown.
     */
    private function previousUnpaidChallans($studentId)
    {
        return Challans::where('student_id', $studentId)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereRaw("LOWER(TRIM(status)) <> 'paid'");
            })
            ->orderByDesc('id')
            ->get()
            ->map(function ($challan) {
                $total = (float) ($challan->total_amount ?? 0);
                $concession = (float) ($challan->concession_amount ?? 0);

                return [
                    'id' => (int) $challan->id,
                    'challan_no' => $challan->challanNo,
                    'challan_type' => $challan->challan_type,
                    'fee_month' => $challan->fee_month,
                    'other_months' => $challan->other_months,
                    'challan_date' => $challan->challan_date,
                    'issue_date' => $challan->issue_date,
                    'due_date' => $challan->due_date,
                    'status' => $challan->status,
                    'total_amount' => round($total, 2),
                    'concession_amount' => round($concession, 2),
                    'payable_amount' => round($total - $concession, 2),
                ];
            })
            ->values()
            ->all();
    }
    private function buildApplicationSnapshot(
        Request $request,
        array $context,
        $flowType,
        array $sourcePlacement,
        array $targetPlacement,
        $billingMonth,
        array $selectedHeadIds
    ) {
        list($policyHeads, $activeConcession) = $this->activeConcessionPolicyMap(
            $context['student']->id
        );

        $tuitionIncrementEnabled = $request->boolean('tuition_increment_enabled', true);
        $tuitionIncrementPercentage = max(
            0,
            (int) $request->input('tuition_increment_percentage', 0)
        );

        $existingStructure = $this->existingFeeStructurePreview(
            $context['student'],
            $sourcePlacement,
            $policyHeads
        );

        $targetStructure = $flowType === 'reactivation'
            ? []
            : $this->targetFeeStructurePreview(
                $context['student'],
                $sourcePlacement,
                $targetPlacement,
                $flowType,
                $policyHeads,
                $tuitionIncrementPercentage,
                $tuitionIncrementEnabled
            );

        $feeRevision = $this->tuitionRevisionPreview(
            $context['student'],
            $sourcePlacement,
            $targetPlacement,
            $policyHeads,
            $tuitionIncrementPercentage,
            $tuitionIncrementEnabled
        );

        /*
         * User may choose the Tuition increment effective month.
         * Billing Month remains the default/fallback.
         */
        $tuitionIncrementEffectiveFrom =
            $this->tuitionIncrementEffectiveFrom(
                $request->input('tuition_increment_effective_from'),
                $billingMonth
            );

        $feeRevision['effective_from'] =
            $tuitionIncrementEffectiveFrom;

        $gapBilling = $this->gapBillingContext(
            $context['student']->id,
            $context['withdrawal']
        );

        $gapBilling = $this->applyReadmissionBillingMonthGapRule(
            $gapBilling,
            $flowType,
            $billingMonth
        );

        $allowedGapMonths = collect($gapBilling['months'] ?? []);
        $selectedGapMonths = [];

        if (\Auth::user()->type === 'company') {
            $selectedGapMonths = collect($request->input('gap_months', []))
                ->map(function ($month) {
                    return Carbon::parse($month)->startOfMonth()->toDateString();
                })
                ->filter(function ($month) use ($allowedGapMonths) {
                    return $allowedGapMonths->contains($month);
                })
                ->unique()
                ->values()
                ->all();
        }

        $existingSelectedHeadIds = collect($existingStructure)
            ->filter(function ($row) {
                return (int) ($row['checked'] ?? 0) === 1;
            })
            ->pluck('head_id')
            ->map(function ($id) { return (int) $id; })
            ->values()
            ->all();

        return [
            'version' => 7,
            'captured_at' => now()->toISOString(),
            'captured_by' => [
                'id' => \Auth::id(),
                'name' => \Auth::user()->name,
                'type' => \Auth::user()->type,
            ],
            'flow' => [
                'type' => $flowType,
                'label' => $this->readmissionFlowLabel($flowType),
                'reactivation_days' => 30,
                'readmission_months' => 3,
                'withdrawal_gap_days' => $this->withdrawalGapDays(
                    $context['withdrawal']
                ),
            ],
            'student' => $this->previewStudentDetails(
                $context['student'],
                $context['enrollment']
            ),
            'withdrawal' => $this->previewWithdrawalDetails(
                $context['withdrawal']
            ),
            'source_placement' => $this->placementDetails($sourcePlacement),
            'target_placement' => $this->placementDetails($targetPlacement),
            'active_policy' => $this->previewPolicyDetails(
                $activeConcession,
                $policyHeads
            ),
            'latest_policy_application' => $this->previewLatestPolicyDetails(
                $this->latestConcessionApplication($context['student']->id)
            ),
            'existing_fee_structure' => $existingStructure,
            'existing_selected_head_ids' => $existingSelectedHeadIds,
            'target_fee_structure' => $targetStructure,
            'selected_head_ids' => array_values($selectedHeadIds),
            'fee_revision' => $feeRevision,
            'gap_months' => $gapBilling['months'],
            'gap_month_statuses' => $gapBilling['month_statuses'],
            'gap_range' => $gapBilling['range'],
            'gap_anchor_challan' => $gapBilling['anchor'],
            'selected_gap_months' => $selectedGapMonths,
            'unpaid_challans' => $this->previousUnpaidChallans(
                $context['student']->id
            ),
            'generate_gap_months' => !empty($selectedGapMonths),
            'request' => [
                'readmission_date' => Carbon::parse($request->readmission_date)->toDateString(),
                'billing_month' => $billingMonth,
                'issue_date' => Carbon::parse($request->issue_date)->toDateString(),
                'due_date' => Carbon::parse($request->due_date)->toDateString(),
                'reason' => $request->reason,
                'tuition_increment_enabled' => (bool) $tuitionIncrementEnabled,
                'tuition_increment_percentage' => (int) $tuitionIncrementPercentage,
                'tuition_increment_effective_from' => $tuitionIncrementEffectiveFrom,
            ],
        ];
    }

    private function fillSnapshotColumns(
        StudentReadmission $record,
        $flowType,
        array $targetPlacement,
        $generateGapMonths,
        array $snapshot,
        array $challanIds
    ) {
        $record->flow_type = $flowType;
        $record->target_section_id = $targetPlacement['section_id'] ?? null;
        $record->generate_gap_months = (bool) $generateGapMonths;
        $record->snapshot = json_encode($snapshot);
        $record->challan_ids = json_encode(array_values(array_unique($challanIds)));
    }

    /**
     * Apply the active bulkchallan() Late Fee rule to the student's
     * PREVIOUS unpaid/Issued challans.
     *
     * Returns the challan IDs that received a new Late Fee head.
     */
    private function applyBulkLateFeeToPreviousUnpaidChallans(
        $studentId,
        array $excludeChallanIds = []
    ): array {
        /*
        |--------------------------------------------------------------------------
        | SHIFA LATE-FEE EXEMPTION
        |--------------------------------------------------------------------------
        |
        | Existing ChallanController rule:
        | register_option == 2 means Shifa and Late Fee must NOT be applied.
        |
        | This check happens before finding/creating any Late Fee head or
        | journal entry, so Readmission approval cannot affect Shifa students'
        | previous unpaid challans.
        |
        */
        $student = StudentRegistration::select(
            'id',
            'register_option'
        )->find($studentId);

        if (
            !$student
            || (int) $student->register_option === 2
        ) {
            return [];
        }

        $lateFeeHead = FeeHead::where(
            'fee_head',
            'LIKE',
            '%LATE FEE%'
        )->first();

        if (!$lateFeeHead) {
            return [];
        }

        $excludeChallanIds = collect(
            $excludeChallanIds
        )
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        /*
         * Same candidate rule as bulkchallan():
         * older challans with status = Issued.
         */
        $issuedChallans = Challans::where(
            'student_id',
            $studentId
        )
            ->whereRaw(
                'LOWER(status) = ?',
                ['issued']
            )
            ->whereNotIn(
                'challan_type',
                [
                    'Registration',
                    'Withdrawal',
                    'Transfer',
                ]
            )
            ->when(
                !empty($excludeChallanIds),
                function ($query) use (
                    $excludeChallanIds
                ) {
                    $query->whereNotIn(
                        'id',
                        $excludeChallanIds
                    );
                }
            )
            ->get();

        if ($issuedChallans->isEmpty()) {
            return [];
        }

        $candidateIds = $issuedChallans
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();

        /*
         * Same duplicate guard as bulkchallan():
         * if Late Fee head already exists, do nothing.
         */
        $alreadyLateFeeIds = ChallanHead::whereIn(
            'challan_id',
            $candidateIds
        )
            ->where(
                'head_id',
                $lateFeeHead->id
            )
            ->pluck('challan_id')
            ->map(fn($id) => (int) $id)
            ->flip();

        $today = Carbon::now()->startOfDay();
        $updatedIds = [];

        foreach ($issuedChallans as $oldChallan) {
            if (
                $alreadyLateFeeIds->has(
                    (int) $oldChallan->id
                )
            ) {
                continue;
            }

            if (empty($oldChallan->due_date)) {
                continue;
            }

            $oldDueDate = Carbon::parse(
                $oldChallan->due_date
            )->startOfDay();

            /*
             * Same weekend adjustment as bulkchallan().
             */
            if ($oldDueDate->isSaturday()) {
                $oldDueDate->addDays(2);
            } elseif ($oldDueDate->isSunday()) {
                $oldDueDate->addDay();
            }

            if ($today->lte($oldDueDate)) {
                continue;
            }

            /*
             * Same calculation:
             * Rs. 120/day, maximum 10 days = Rs. 1,200.
             */
            $daysOverdue = min(
                $today->diffInDays($oldDueDate),
                10
            );

            $lateFeeAmount =
                $daysOverdue * 120;

            if ($lateFeeAmount <= 0) {
                continue;
            }

            $lateFeeHeadRecord =
                ChallanHead::create([
                    'challan_id' =>
                        $oldChallan->id,
                    'head_id' =>
                        $lateFeeHead->id,
                    'price' =>
                        $lateFeeAmount,
                    'concession' => 0,
                    'paid' => 0,
                ]);

            JournalItem::insert([
                [
                    'journal' =>
                        $oldChallan->voucher_id,
                    'account' =>
                        $lateFeeHead->account_id,
                    'head' =>
                        $lateFeeHead->id,
                    'entry_id' =>
                        $lateFeeHeadRecord->id,
                    'model_id' =>
                        $oldChallan->id,
                    'model_type' =>
                        Challans::class,
                    'user_id' =>
                        $oldChallan->student_id,
                    'user_type' =>
                        'Student',
                    'types' =>
                        'Challan',
                    'credit' =>
                        $lateFeeAmount,
                    'debit' => 0,
                    'description' =>
                        'Late Fee Income - Challan No '
                        . $oldChallan->challanNo,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'added_by' =>
                        $oldChallan->created_by,
                    'added_at' => now(),
                ],
                [
                    'journal' =>
                        $oldChallan->voucher_id,
                    'account' =>
                        $lateFeeHead
                            ->receivable_account_id,
                    'head' =>
                        $lateFeeHead->id,
                    'entry_id' =>
                        $lateFeeHeadRecord->id,
                    'model_id' =>
                        $oldChallan->id,
                    'model_type' =>
                        Challans::class,
                    'user_id' =>
                        $oldChallan->student_id,
                    'user_type' =>
                        'Student',
                    'types' =>
                        'Challan',
                    'credit' => 0,
                    'debit' =>
                        $lateFeeAmount,
                    'description' =>
                        'Late Fee Receivable - Challan No '
                        . $oldChallan->challanNo,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'added_by' =>
                        $oldChallan->created_by,
                    'added_at' => now(),
                ],
            ]);

            DB::table('challans')
                ->where(
                    'id',
                    $oldChallan->id
                )
                ->increment(
                    'total_amount',
                    $lateFeeAmount
                );

            $updatedIds[] =
                (int) $oldChallan->id;

            $alreadyLateFeeIds->put(
                (int) $oldChallan->id,
                true
            );
        }

        return array_values(
            array_unique($updatedIds)
        );
    }

    /**
     * Challans generated by approval for this application.
     *
     * Supports:
     * - record.challan_id          (main challan)
     * - record.challan_ids         (main + gap Regular)
     * - approval_snapshot.generated_challan_ids
     */
    private function generatedReadmissionChallans(
        StudentReadmission $record
    ) {
        $ids = collect();

        if (!empty($record->challan_id)) {
            $ids->push((int) $record->challan_id);
        }

        $recordChallanIds = $this->decodeJsonColumn(
            $record->challan_ids
        );

        $ids = $ids->merge(
            is_array($recordChallanIds)
                ? $recordChallanIds
                : []
        );

        $approvalSnapshot = $this->decodeJsonColumn(
            $record->approval_snapshot
        );

        $ids = $ids->merge(
            $approvalSnapshot['generated_challan_ids']
                ?? []
        )
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $order = $ids->flip();

        return Challans::whereIn('id', $ids->all())
            ->get()
            ->sortBy(function ($challan) use ($order) {
                return $order->get(
                    (int) $challan->id,
                    999999
                );
            })
            ->values();
    }

    private function decodeJsonColumn($value)
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function studentFeeStructureState($studentId)
    {
        return StudentFeeStructure::with('feehead')
            ->where('reg_id', $studentId)
            ->orderBy('id')
            ->get()
            ->mapWithKeys(function ($row) {
                $amount = (float) ($row->amount ?? 0);
                $discount = (float) ($row->discount ?? 0);

                return [
                    (int) $row->head_id => [
                        'id' => $row->id,
                        'head_id' => (int) $row->head_id,
                        'fee_head' => optional($row->feehead)->fee_head
                            ?: ('Head #' . $row->head_id),
                        'amount' => $amount,
                        'discount' => $discount,
                        'checked_status' => (int) ($row->checked_status ?? 0),
                        'is_custom' => (int) ($row->is_custom ?? 0),
                        'payable_amount' => round(
                            $amount - (($amount * $discount) / 100),
                            2
                        ),
                    ],
                ];
            });
    }

    private function applyReadmissionStudentStructureRevision(
        $student,
        array $targetPlacement,
        array $feeRevision
    ) {
        $branchId = (int) ($targetPlacement['branch_id'] ?? $student->owned_by);
        $classId = (int) ($targetPlacement['class_id'] ?? $student->class_id);

        /*
         * Readmission preserves the student's existing fee amounts.
         * Only placement metadata changes here.
         */
        StudentFeeStructure::where('reg_id', $student->id)
            ->update([
                'owned_by' => $branchId,
                'branch_id' => $branchId,
                'class_id' => $classId,
            ]);

        /*
         * Session change Tuition revision:
         * base = student's current Tuition
         * new  = base + entered integer %
         */
        if (
            empty($feeRevision['session_changed'])
            || empty($feeRevision['enabled'])
            || empty($feeRevision['head_id'])
        ) {
            return;
        }

        $structure = StudentFeeStructure::where('reg_id', $student->id)
            ->where('head_id', (int) $feeRevision['head_id'])
            ->orderByDesc('id')
            ->first();

        if (!$structure) {
            return;
        }

        $structure->amount = (float) ($feeRevision['new_base_amount'] ?? $structure->amount);
        $structure->owned_by = $branchId;
        $structure->branch_id = $branchId;
        $structure->class_id = $classId;

        if ((int) ($feeRevision['percentage'] ?? 0) !== 0) {
            $structure->is_custom = 1;
        }

        $structure->save();
    }

    private function recordReadmissionPromotionAndFeeRevision(
        $student,
        $enrollment,
        StudentReadmission $record,
        array $source,
        array $target,
        array $snapshot,
        $flowType,
        $beforeStructure
    ) {
        $sessionChanged = (int) ($source['session_id'] ?? 0)
            !== (int) ($target['session_id'] ?? 0);
        $classChanged = (int) ($source['class_id'] ?? 0)
            !== (int) ($target['class_id'] ?? 0);
        $sectionChanged = (int) ($source['section_id'] ?? 0)
            !== (int) ($target['section_id'] ?? 0);
        $branchChanged = (int) ($source['branch_id'] ?? 0)
            !== (int) ($target['branch_id'] ?? 0);

        if (!$sessionChanged && !$classChanged && !$sectionChanged && !$branchChanged) {
            return;
        }

        /*
         * Placement/session promotion effective date.
         */
        $effectiveDate = $snapshot['request']['readmission_date']
            ?? $record->readmission_date
            ?? date('Y-m-d');

        /*
         * Fee increment effective month.
         *
         * IMPORTANT:
         * Tuition revision starts from the selected Billing Month onward.
         * Earlier gap months remain on the student's previous fee amount.
         */
        $feeRevisionEffectiveFrom =
            $snapshot['fee_revision']['effective_from']
            ?? $snapshot['request']['billing_month']
            ?? $effectiveDate;

        $feeRevisionEffectiveFrom = Carbon::parse(
            $feeRevisionEffectiveFrom
        )->startOfMonth()->toDateString();

        $studentReference = optional($enrollment)->enrollId
            ?: $student->roll_no
            ?: $student->id;

        /*
        |--------------------------------------------------------------------------
        | Same promotion table used by StudentPromotions controller
        |--------------------------------------------------------------------------
        */
        $promotion = new \App\Models\StudentPromotions();
        $promotion->student_id = $studentReference;
        $promotion->prev_session = $source['session_id'] ?? null;
        $promotion->new_session = $target['session_id'] ?? null;
        $promotion->class_from = $source['class_id'] ?? null;
        $promotion->class_to = $target['class_id'] ?? null;
        $promotion->prev_section = $source['section_id'] ?? null;
        $promotion->new_section = $target['section_id'] ?? null;
        $promotion->branch_from = $source['branch_id'] ?? null;
        $promotion->branch_to = $target['branch_id'] ?? null;
        $promotion->promotion_date = $effectiveDate;
        $promotion->owned_by = $target['branch_id'] ?? $record->owned_by;
        $promotion->created_by = \Auth::user()->creatorId();
        $promotion->save();

        /*
        |--------------------------------------------------------------------------
        | Promotion/transfer history entry
        |--------------------------------------------------------------------------
        */
        $promotionHistory = new StudentHistory();
        $promotionHistory->reg_id = $student->id;
        $promotionHistory->student_id = $studentReference;
        $promotionHistory->event_type = $branchChanged ? 'transfer' : 'promote';
        $promotionHistory->from_session_id = $source['session_id'] ?? null;
        $promotionHistory->from_class_id = $source['class_id'] ?? null;
        $promotionHistory->from_section_id = $source['section_id'] ?? null;
        $promotionHistory->from_branch_id = $source['branch_id'] ?? null;
        $promotionHistory->to_session_id = $target['session_id'] ?? null;
        $promotionHistory->to_class_id = $target['class_id'] ?? null;
        $promotionHistory->to_section_id = $target['section_id'] ?? null;
        $promotionHistory->to_branch_id = $target['branch_id'] ?? null;
        $promotionHistory->effective_date = $effectiveDate;
        $promotionHistory->remarks = (
            $branchChanged
                ? 'Readmission Branch Promotion'
                : 'Readmission Promotion'
        ) . ' - ' . $this->readmissionFlowLabel($flowType);
        $promotionHistory->owned_by = $target['branch_id'] ?? $record->owned_by;
        $promotionHistory->created_by = \Auth::user()->creatorId();
        $promotionHistory->save();

        /*
        |--------------------------------------------------------------------------
        | Fee revision batch
        |--------------------------------------------------------------------------
        */
        $batch = StudentFeeRevisionBatch::create([
            'revision_type' => $branchChanged
                ? 'branch_promotion'
                : 'promotion',
            'promotion_id' => $promotion->id,
            'student_id' => $studentReference,
            'reg_id' => $student->id,
            'session_from_id' => $source['session_id'] ?? null,
            'session_to_id' => $target['session_id'] ?? null,
            'branch_from_id' => $source['branch_id'] ?? null,
            'branch_to_id' => $target['branch_id'] ?? null,
            'class_from_id' => $source['class_id'] ?? null,
            'class_to_id' => $target['class_id'] ?? null,
            'section_from_id' => $source['section_id'] ?? null,
            'section_to_id' => $target['section_id'] ?? null,
            'effective_from' => $feeRevisionEffectiveFrom,
            'status' => 'applied',
            'remarks' => 'Readmission placement/session revision; Tuition fee revision effective from '
                . Carbon::parse($feeRevisionEffectiveFrom)->format('F Y'),
            'owned_by' => $target['branch_id'] ?? $record->owned_by,
            'created_by' => \Auth::user()->creatorId(),
        ]);

        /*
         * Tuition revision is checked by default on session change.
         * Even 0% is recorded so the audit trail shows that the fee revision
         * was reviewed and intentionally applied with no increase.
         */
        $feeRevision = $snapshot['fee_revision'] ?? [];

        if (
            $sessionChanged
            && !empty($feeRevision['enabled'])
            && !empty($feeRevision['head_id'])
        ) {
            $headId = (int) $feeRevision['head_id'];
            $before = $beforeStructure->get($headId, []);
            $after = StudentFeeStructure::where('reg_id', $student->id)
                ->where('head_id', $headId)
                ->orderByDesc('id')
                ->first();

            $prevBase = (float) (
                $feeRevision['prev_base_amount']
                ?? $before['amount']
                ?? 0
            );
            $newBase = (float) (
                $feeRevision['new_base_amount']
                ?? optional($after)->amount
                ?? $prevBase
            );
            $prevPayable = (float) (
                $feeRevision['prev_payable_amount']
                ?? $before['payable_amount']
                ?? $prevBase
            );
            $newPayable = (float) (
                $feeRevision['new_payable_amount']
                ?? $newBase
            );

            StudentFeeRevisionItem::create([
                'batch_id' => $batch->id,
                'student_fee_structure_id' => optional($after)->id
                    ?: ($before['id'] ?? null),
                'student_id' => $studentReference,
                'reg_id' => $student->id,
                'head_id' => $headId,
                'percentage' => max(
                    0,
                    (int) ($feeRevision['percentage'] ?? 0)
                ),
                'prev_base_amount' => $prevBase,
                'new_base_amount' => $newBase,
                'prev_payable_amount' => $prevPayable,
                'new_payable_amount' => $newPayable,
            ]);
        }
    }

    private function attachStructureFromSnapshot(
        $student,
        array $targetPlacement,
        array $targetRows,
        array $selectedHeadIds
    ) {
        $branchId = (int) $targetPlacement['branch_id'];
        $classId = (int) $targetPlacement['class_id'];
        $selectedHeadIds = array_map('intval', $selectedHeadIds);

        StudentFeeStructure::where('reg_id', $student->id)
            ->update(['checked_status' => 0]);

        foreach ($targetRows as $row) {
            $headId = (int) ($row['head_id'] ?? 0);

            if (!$headId) {
                continue;
            }

            $structure = StudentFeeStructure::where('reg_id', $student->id)
                ->where('head_id', $headId)
                ->orderByDesc('id')
                ->first();

            if (!$structure) {
                $structure = new StudentFeeStructure();
                $structure->reg_id = $student->id;
                $structure->student_id = $student->roll_no;
                $structure->head_id = $headId;
                $structure->created_by = $student->created_by
                    ?: \Auth::user()->creatorId();
            }

            $structure->branch_id = $branchId;
            $structure->class_id = $classId;
            $structure->owned_by = $branchId;

            /*
             * A session-promotion Tuition row may carry charge_amount based on
             * the existing student's Tuition + percentage. Use that revised
             * student amount instead of the ClassWiseFee reference amount.
             */
            $structure->amount = (float) (
                $row['charge_amount']
                ?? $row['class_amount']
                ?? 0
            );

            $structure->discount = (float) ($row['base_discount'] ?? 0);
            $structure->checked_status = in_array(
                $headId,
                $selectedHeadIds,
                true
            ) ? 1 : 0;

            if (
                ($row['charge_source'] ?? '') === 'student_fee_structure_revision'
                && (int) ($row['increment_percentage'] ?? 0) !== 0
            ) {
                $structure->is_custom = 1;
            }

            $structure->save();
        }

        /*
         * Promotion semantics: the student's fee structure is one continuing
         * structure. After target placement is approved, all rows move to the
         * new branch/class metadata; there is no session filter on SFS.
         */
        StudentFeeStructure::where('reg_id', $student->id)
            ->update([
                'owned_by' => $branchId,
                'branch_id' => $branchId,
                'class_id' => $classId,
            ]);
    }
    private function createAdmissionChallanFromSnapshot(
        $student,
        array $targetPlacement,
        array $requestSnapshot,
        array $targetRows,
        array $selectedHeadIds,
        array $policySnapshot,
        $flowType = 'reenrollment'
    ) {
        $billingMonth = Carbon::parse(
            $requestSnapshot['billing_month']
            ?? $requestSnapshot['readmission_date']
            ?? date('Y-m-d')
        )->startOfMonth()->toDateString();

        $isReadmission = strtolower((string) $flowType) === 'readmission';

        $challanType = $isReadmission
            ? 'Re-Admission'
            : 'Admission';

        $existing = Challans::where('student_id', $student->id)
            ->where('challan_type', $challanType)
            ->whereRaw(
                "STR_TO_DATE(fee_month, '%Y-%m-%d') = ?",
                [$billingMonth]
            )
            ->first();

        if ($existing) {
            throw new \RuntimeException(
                'A ' . $challanType . ' challan already exists for ' . $billingMonth . '. Approval stopped to prevent duplicate billing.'
            );
        }

        $selectedIds = array_map('intval', $selectedHeadIds);

        /*
         * Final challan rows use the saved final selection EXACTLY.
         *
         * Branch restrictions are already enforced when Branch submits:
         * - Readmission Admission/Security are removed.
         * - Readmission Tuition is forced when required.
         * - Re-enrollment mandatory Branch heads are merged.
         *
         * Company/HO review is unrestricted, so Company may add/remove ANY
         * head before approval. Do not re-apply Branch rules here.
         */
        $rows = collect($targetRows)
            ->filter(function ($row) use ($selectedIds) {
                return in_array(
                    (int) ($row['head_id'] ?? 0),
                    $selectedIds,
                    true
                );
            })
            ->values();

        if ($rows->isEmpty()) {
            throw new \RuntimeException(
                'No chargeable selected fee rows were found in the saved snapshot.'
            );
        }

        $challan = new Challans();
        $challan->student_id = $student->id;
        $challan->class_id = $targetPlacement['class_id'];
        $challan->rollno = $student->roll_no;
        $challan->challanNo = $this->challanNo();
        $challan->challan_date = Carbon::parse(
            $requestSnapshot['readmission_date']
        )->toDateString();
        $challan->fee_month = $billingMonth;
        $challan->challan_type = $challanType;
        $challan->issue_date = Carbon::parse(
            $requestSnapshot['issue_date']
        )->toDateString();
        $challan->due_date = Carbon::parse(
            $requestSnapshot['due_date']
        )->toDateString();
        $challan->status = 'Issued';
        $challan->session_id = $targetPlacement['session_id'];
        $challan->section_id = $targetPlacement['section_id'];
        $challan->owned_by = $targetPlacement['branch_id'];
        $challan->created_by = \Auth::user()->creatorId();
        $challan->total_amount = 0;
        $challan->concession_amount = 0;
        $challan->concession_id = $policySnapshot['concession_id'] ?? null;
        $challan->save();

        $total = 0;
        $concessionTotal = 0;
        $items = [];

        foreach ($rows as $index => $row) {
            /*
             * class_amount in the saved charge snapshot is already the amount
             * that the frontend showed under "Will Charge".
             *
             * For Readmission Tuition this is the student's existing Tuition
             * amount (plus an optional promotion-style increment), NOT the
             * target ClassWiseFee amount.
             */
            $baseAmount = (float) (
                $row['charge_amount']
                ?? $row['class_amount']
                ?? 0
            );

            $discount = (float) ($row['discount'] ?? 0);
            $lineConcession = round(
                ($baseAmount * $discount) / 100
            );

            $challanHead = new ChallanHead();
            $challanHead->challan_id = $challan->id;
            $challanHead->head_id = (int) $row['head_id'];
            $challanHead->price = $baseAmount;
            $challanHead->concession = $lineConcession;
            $challanHead->save();

            $total += $baseAmount;
            $concessionTotal += $lineConcession;

            $items[$index] = [
                'prod_id' => $challanHead->id,
                'head' => (int) $row['head_id'],
                'price' => $baseAmount,
                'quantity' => 1,
                'concession' => $lineConcession,
                'total' => $baseAmount,
            ];
        }

        $challan->concession_amount = $concessionTotal;
        $challan->total_amount = $total;
        $challan->save();

        $branch = User::find($targetPlacement['branch_id']);

        $challan->voucher_id = Utility::jrentry([
            'id' => $challan->id,
            'no' => $challan->challanNo,
            'date' => $challan->challan_date,
            'reference' => $challan->student_id,
            'category' => $challanType,
            'user_id' => $challan->student_id,
            'std_name' => $student->stdname,
            'branch_name' => optional($branch)->name,
            'fee_month' => $challan->fee_month,
            'bank_name' => '',
            'user_type' => 'Student',
            'owned_by' => $challan->owned_by,
            'created_by' => $challan->created_by,
            'items' => $items,
        ]);

        $challan->save();

        return $challan;
    }

    /**
     * Single-student/single-month equivalent of BulkChallan PATH B.
     * It deliberately reuses the same Regular-challan head rules without
     * calling bulkchallan(), so readmission does not depend on bulk filters.
     */
    /**
     * Build historical/projected Regular rows for Readmission approval without
     * mutating StudentFeeStructure.
     *
     * Before increment-effective month: existing student amount.
     * Effective month onward: approved revised Tuition amount.
     */
    private function readmissionApprovalRegularRows(
        array $snapshot,
        $monthDate
    ) {
        $rows = collect(
            $snapshot['existing_fee_structure']
            ?? []
        )
            ->filter(function ($row) {
                return (int) (
                    $row['checked'] ?? 0
                ) === 1;
            })
            ->values();

        $feeRevision =
            $snapshot['fee_revision']
            ?? [];

        if (
            empty($feeRevision['session_changed'])
            || empty($feeRevision['enabled'])
            || empty($feeRevision['head_id'])
        ) {
            return $rows->all();
        }

        $effectiveMonth = Carbon::parse(
            $feeRevision['effective_from']
            ?? $snapshot['request']['billing_month']
            ?? date('Y-m-d')
        )->startOfMonth();

        $chargeMonth = Carbon::parse(
            $monthDate
        )->startOfMonth();

        if ($chargeMonth->lt($effectiveMonth)) {
            return $rows->all();
        }

        $revisionHeadId =
            (int) $feeRevision['head_id'];

        return $rows
            ->map(function ($row) use (
                $revisionHeadId,
                $feeRevision
            ) {
                if (
                    (int) ($row['head_id'] ?? 0)
                    !== $revisionHeadId
                ) {
                    return $row;
                }

                $newBase = (float) (
                    $feeRevision['new_base_amount']
                    ?? $row['class_amount']
                    ?? 0
                );

                $discount = (float) (
                    $row['discount']
                    ?? 0
                );

                $row['class_amount'] =
                    $newBase;

                $row['payable_amount'] =
                    round(
                        $newBase
                        - (
                            ($newBase * $discount)
                            / 100
                        ),
                        2
                    );

                $row['source'] =
                    'approved_fee_revision';

                return $row;
            })
            ->values()
            ->all();
    }

    /**
     * Build Regular rows for Re-enrollment approval from approved target
     * snapshot only. No StudentFeeStructure mutation is required.
     */
    private function reEnrollmentApprovalRegularRows(
        array $snapshot,
        array $selectedHeadIds
    ) {
        $selectedHeadIds =
            array_map(
                'intval',
                $selectedHeadIds
            );

        return collect(
            $snapshot['target_fee_structure']
            ?? []
        )
            ->filter(function ($row) use (
                $selectedHeadIds
            ) {
                return in_array(
                    (int) (
                        $row['head_id'] ?? 0
                    ),
                    $selectedHeadIds,
                    true
                );
            })
            ->map(function ($row) {
                $row['class_amount'] =
                    (float) (
                        $row['charge_amount']
                        ?? $row['class_amount']
                        ?? 0
                    );

                $row['checked'] = 1;

                return $row;
            })
            ->values()
            ->all();
    }

    private function generateRegularChallanFromSnapshot(
        $student,
        array $placement,
        $monthDate,
        array $structureRows,
        array $policySnapshot,
        $includeAdmissionCoverage = false
    ) {
        $monthDate = Carbon::parse($monthDate)->startOfMonth()->toDateString();

        if ($this->regularOrAdvanceExistsForMonth($student->id, $monthDate)) {
            return null;
        }

        if (
            $includeAdmissionCoverage
            && $this->admissionWithTuitionExistsForMonth($student->id, $monthDate)
        ) {
            return null;
        }

        $rows = collect($structureRows)
            ->filter(function ($row) {
                return !$this->isSkippableRegularHead(
                    strtolower((string) ($row['fee_head'] ?? ''))
                );
            })
            ->values();

        if ($rows->isEmpty()) {
            return null;
        }

        $issueDate = Carbon::parse($monthDate)
            ->subMonthNoOverflow()
            ->day(26)
            ->toDateString();

        $dueDateCarbon = Carbon::parse($monthDate)->day(8);
        if ($dueDateCarbon->isSaturday()) {
            $dueDateCarbon->addDays(2);
        } elseif ($dueDateCarbon->isSunday()) {
            $dueDateCarbon->addDay();
        }
        $dueDate = $dueDateCarbon->toDateString();

        $challan = new Challans();
        $challan->student_id = $student->id;
        $challan->rollno = $student->roll_no;
        $challan->class_id = $placement['class_id'] ?? $student->class_id;
        $challan->challanNo = $this->challanNo();
        $challan->challan_date = date('Y-m-d');
        $challan->section_id = $placement['section_id'] ?? optional($student->enrollment)->section_id;
        $challan->fee_month = $monthDate;
        $challan->challan_type = 'Regular';
        $challan->total_amount = 0;
        $challan->issue_date = $issueDate;
        $challan->due_date = $dueDate;
        $challan->status = 'Issued';
        $challan->owned_by = $placement['branch_id'] ?? $student->owned_by;
        $challan->created_by = $student->created_by ?: \Auth::user()->creatorId();
        $challan->session_id = $placement['session_id'] ?? $student->session_id;
        $challan->concession_id = $policySnapshot['concession_id'] ?? null;
        $challan->concession_amount = 0;
        $challan->other_months = null;
        $challan->save();

        $total = 0;
        $concessionAmount = 0;
        $items = [];

        foreach ($rows as $index => $row) {
            $baseAmount = (float) ($row['class_amount'] ?? 0);
            $discount = (float) ($row['discount'] ?? 0);
            $lineConcession = round(($baseAmount / 100) * $discount);

            $challanHead = new ChallanHead();
            $challanHead->challan_id = $challan->id;
            $challanHead->head_id = (int) $row['head_id'];
            $challanHead->price = $baseAmount;
            $challanHead->concession = $lineConcession;
            $challanHead->save();

            $total += $baseAmount;
            $concessionAmount += $lineConcession;

            $items[$index] = [
                'prod_id' => $challanHead->id,
                'head' => (int) $row['head_id'],
                'price' => $baseAmount,
                'quantity' => 1,
                'concession' => $lineConcession,
                'total' => $baseAmount,
            ];
        }

        $challan->concession_amount = $concessionAmount;
        $challan->total_amount = $total;
        $challan->save();

        $challan->voucher_id = Utility::jrentry([
            'id' => $challan->id,
            'no' => $challan->challanNo,
            'date' => $challan->challan_date,
            'reference' => $challan->student_id,
            'category' => 'Regular',
            'user_id' => $student->id,
            'user_type' => 'Student',
            'owned_by' => $challan->owned_by,
            'created_by' => $challan->created_by,
            'items' => $items,
        ]);
        $challan->save();

        return $challan;
    }

    private function regularOrAdvanceChallanForMonth($studentId, $monthDate)
    {
        return Challans::where('student_id', $studentId)
            ->whereIn('challan_type', ['Regular', 'Advance'])
            ->where(function ($query) use ($monthDate) {
                $query->whereRaw(
                    "STR_TO_DATE(fee_month, '%Y-%m-%d') = ?",
                    [$monthDate]
                )
                    ->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(other_months, ' ', ''))",
                        [$monthDate]
                    );
            })
            ->orderByRaw(
                "CASE WHEN LOWER(TRIM(challan_type)) = 'advance' THEN 0 ELSE 1 END"
            )
            ->orderByDesc('id')
            ->first();
    }

    private function regularOrAdvanceExistsForMonth($studentId, $monthDate)
    {
        return (bool) $this->regularOrAdvanceChallanForMonth(
            $studentId,
            $monthDate
        );
    }

    private function admissionWithTuitionExistsForMonth($studentId, $monthDate)
    {
        $tuitionHead = FeeHead::where('fee_head', 'LIKE', '%Tuition%')->first();

        if (!$tuitionHead) {
            return false;
        }

        return Challans::where('student_id', $studentId)
            ->whereIn('challan_type', ['Admission', 'Re-Admission'])
            ->where(function ($query) use ($monthDate) {
                $query->whereRaw(
                    "STR_TO_DATE(fee_month, '%Y-%m-%d') = ?",
                    [$monthDate]
                )
                    ->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(other_months, ' ', ''))",
                        [$monthDate]
                    );
            })
            ->whereHas('heads', function ($query) use ($tuitionHead) {
                $query->where('head_id', $tuitionHead->id);
            })
            ->exists();
    }

    private function isSkippableRegularHead($name)
    {
        return str_contains($name, 'security')
            || str_contains($name, 're-admission')
            || str_contains($name, 'readmission')
            || str_contains($name, 'transfer')
            || str_contains($name, 'admission')
            || str_contains($name, 'registration');
    }

    private function approveLegacyReadmission(StudentReadmission $record)
    {
        $sectionId = optional($record->challan)->section_id;

        StudentRegistration::where('roll_no', $record->student_id)
            ->update([
                'class_id' => $record->class_id,
                'section_id' => $sectionId,
                'student_status' => 'Enrolled',
                'active_status' => 1,
                'session_id' => $record->session_id,
                'owned_by' => $record->branch_id,
                'branch' => $record->branch_id,
            ]);

        StudentEnrollments::where('enrollId', $record->student_id)
            ->update([
                'owned_by' => $record->branch_id,
                'active_status' => 1,
                'class_id' => $record->class_id,
                'section_id' => $sectionId,
                'session_id' => $record->session_id,
            ]);

        $record->status = 'approved';
        $record->save();
    }

    private function reactivateFromReadmission($withdrawal, $effectiveDate, $remarks)
    {
        $student = StudentRegistration::findOrFail($withdrawal->student_id);
        $enrollment = StudentEnrollments::where('regId', $student->id)->first();

        $history = new StudentHistory();
        $history->reg_id = $student->id;
        $history->student_id = optional($enrollment)->enrollId;
        $history->event_type = 'reactivate';
        $history->from_session_id = $withdrawal->session_id ?: $student->session_id;
        $history->from_class_id = $withdrawal->class_id ?: $student->class_id;
        $history->from_branch_id = $withdrawal->branch_id ?: $student->owned_by;
        $history->to_session_id = $student->session_id;
        $history->to_class_id = $student->class_id;
        $history->to_branch_id = $student->owned_by;
        $history->effective_date = $effectiveDate
            ? Carbon::parse($effectiveDate)->toDateString()
            : date('Y-m-d');
        $history->remarks = $remarks ?: (
            'Withdrawal reactivated from application dated '
            . ($withdrawal->apply_date
                ? Carbon::parse($withdrawal->apply_date)->format('d-m-Y')
                : 'N/A')
        );
        $history->owned_by = $student->owned_by;
        $history->created_by = \Auth::user()->creatorId();
        $history->save();

        $student->active_status = 1;
        $student->student_status = 'Enrolled';
        $student->save();

        if ($enrollment) {
            $enrollment->active_status = 1;
            $enrollment->save();
        }

        $applyDate = $withdrawal->apply_date
            ? Carbon::parse($withdrawal->apply_date)
            : null;

        $withdrawalChallan = Challans::where('student_id', $student->id)
            ->where('challan_type', 'Withdrawal')
            ->when($applyDate, function ($query) use ($applyDate) {
                $query->whereDate(
                    'fee_month',
                    $applyDate->copy()->startOfMonth()->toDateString()
                )
                    ->whereDate(
                        'challan_date',
                        $applyDate->toDateString()
                    );
            })
            ->first();

        if ($withdrawalChallan) {
            ChallanHead::where('challan_id', $withdrawalChallan->id)->delete();
            $withdrawalChallan->delete();
        }

        $withdrawal->status = 'reactive';
        $withdrawal->save();
    }
}
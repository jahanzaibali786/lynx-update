<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChallanHead;
use App\Models\Challans;
use App\Models\ChartOfAccount;
use App\Models\Classes;
use App\Models\ClassSection;
use App\Models\ClassWiseFee;
use App\Models\Concession;
use App\Models\ConcessionPolicy;
use App\Models\ConcessionPolicyHead;
use App\Models\EmpChildrens;
use App\Models\Employee;
use App\Models\FeeHead;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Session;
use App\Models\StudentEnrollments;
use App\Models\StudentFeeStructure;
use App\Models\StudentReceipt;
use App\Models\StudentRegistration;
use App\Models\User;
use App\Models\Utility;
use Auth;
use Carbon\Carbon;
use Date;
use App\Models\StudentFeeRevisionBatch;
use App\Models\StudentFeeRevisionItem;
// use Dompdf\Dompdf;
// use Dompdf\Options;
// use Barryvdh\DomPDF\Facade as PDF;
// use Barryvdh\DomPDF\PDF;
use DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use mikehaertl\wkhtmlto\Pdf;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class ChallanController extends Controller
{
    public function challanlist(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
        }
        $session = [];
        $class = [];
        if (! empty($request->branches)) {
            $session = Session::where('owned_by', '=', $request->branches)->get()->pluck('year', 'id');
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            // $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
        }
        $challans = Challans::get();

        return view('challans.challanlist', compact('challans', 'branches', 'session', 'class'));
    }

    public function challanedit(Request $request, $id)
    {
        $challan = Challans::findOrFail($id);

        return view('challans.edit', compact('challan'));
    }

    public function index($id = null)
    {
        // dd($id);
        return view('challans.challanform', compact('id'));
    }

    // public function index(){
    //     return view('challans.challan');
    // }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'student_id' => 'required',
            'challan_date' => 'required|date',
            'challanType' => 'required',
            'heads' => 'required|array',
            'issueDate' => 'required|date',
            'dueDate' => 'required|date',
        ]);
        DB::beginTransaction();
        try {
            $total = 0;
            $concession = 0;
            $item = [];
            $std = StudentEnrollments::where('regId', $request->student_id)->first();
            $challan = new Challans;
            $challan->student_id = $validatedData['student_id'];
            $challan->class_id = $std->class_id;
            $challan->rollno = $std->enrollId;
            $challan->challanNo = $this->challanNo();
            $challan->challan_date = $validatedData['challan_date'];
            $challan->challan_type = $validatedData['challanType'];
            $challan->issue_date = $validatedData['issueDate'];
            $challan->due_date = $validatedData['dueDate'];
            $challan->fee_month = date('Y-m-01');
            $challan->status = 'Issued';
            $challan->owned_by = $std->owned_by;
            $challan->created_by = \Auth::user()->creatorId();
            $challan->save();

            $itemIndex = 0;
            $concessiondata = Concession::where('student_id', $request->student_id)->where('end_date', '>=', date('Y-m-d'))->where('status', 'Approved')->first();

            foreach ($request->heads as $headId) {
                $fee = ClassWiseFee::where('head_id', $headId)->first();
                if ($concessiondata) {
                    $conession_head = ConcessionPolicyHead::where('head_id', $headId)->where('concession_id', $concessiondata->concession_id)->first();
                    $concessionAmount = ($fee->amount / 100) * $conession_head->percentage;
                }
                $challan_head = new ChallanHead;
                $challan_head->challan_id = $challan->id;
                $challan_head->head_id = $headId;
                $challan_head->price = $fee ? $fee->amount : 0;
                $challan_head->concession = @$concessionAmount ? @$concessionAmount : 0;
                $challan_head->save();

                $total += $fee->amount;
                $concession += @$concessionAmount ? @$concessionAmount : 0;
                $item[$itemIndex]['head'] = $headId;
                $item[$itemIndex]['price'] = $fee ? $fee->amount : 0;
                $item[$itemIndex]['quantity'] = 1;
                $item[$itemIndex]['concession'] = @$concessionAmount ? @$concessionAmount : 0;
                $item[$itemIndex]['total'] = $total;
                $itemIndex++;
                $head = FeeHead::findOrFail($headId);
                $heads[] = [
                    'name' => $head->fee_head,
                    'amount' => $fee ? $fee->amount : 0,
                ];
            }

            $challan->concession_id = @$concessiondata ? @$concessiondata->concession_id : '';
            $challan->concession_amount = @$concession ? @$concession : 0;
            $challan->total_amount = $total;
            $challan->save();

            $data['id'] = $challan->id;
            $data['date'] = $challan->challan_date;
            $data['reference'] = $challan->student_id;
            $data['category'] = 'Withdrawal';
            $data['owned_by'] = $challan->owned_by;
            $data['created_by'] = $challan->created_by;
            $data['items'] = $item;

            // dd($data);
            $dataret = Utility::jrentry($data);
            DB::commit();

            return redirect()->route('challan.show', ['id' => $challan->id])->with('Challan has been created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);

            return redirect()->back()->with('error', $e);
        }
    }
public function legacyShow($id, Request $request)
    {
        $challan = Challans::with([
            'heads.feeHead',
            'branch',
            'class',
            'enrollstudent.session',
            'enrollstudent.section',
            'student.session',
            'student.enrollment.session',
            'student.enrollment.section',
        ])->findOrFail($id);

        $student = $challan->student;
        $isIssued = strtolower((string) $challan->status) === 'issued';
        $canEdit = Auth::user()->type === 'company' && $isIssued;
        $canRollback = in_array(Auth::user()->type, ['company', 'super admin']) && $isIssued;
        $editMode = $request->get('mode') === 'edit' && $canEdit;

        $enrollSession = optional($challan->enrollstudent)->session;
        $studentSession = optional($student)->session;
        $defaultSessionId = $challan->session_id ?: optional($enrollSession)->id ?: optional($studentSession)->id;
        $selectedSessionId = $request->input('session_id', $defaultSessionId);
        $session = $selectedSessionId ? Session::find($selectedSessionId) : null;

        $sessions = Session::where(function ($query) use ($challan) {
            $query->where('owned_by', $challan->owned_by)
                ->orWhere('created_by', $challan->created_by);
        })
            ->orderByDesc('id')
            ->get()
            ->pluck('year', 'id')
            ->prepend('Select Session', '');

        $revisionBatches = StudentFeeRevisionBatch::with([
            'items.feehead',
            'sessionFrom',
            'sessionTo',
            'branchFrom',
            'branchTo',
            'classFrom',
            'classTo',
        ])
            ->where('reg_id', $challan->student_id)
            ->when($selectedSessionId, function ($query) use ($selectedSessionId) {
                $query->where(function ($q) use ($selectedSessionId) {
                    $q->where('session_from_id', $selectedSessionId)
                        ->orWhere('session_to_id', $selectedSessionId);
                });
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get();

        $revisionOptionsByHead = collect();
        foreach ($revisionBatches as $batch) {
            foreach ($batch->items as $item) {
                $revisionOptionsByHead->push([
                    'head_id' => (int) $item->head_id,
                    'head_name' => $item->feehead->fee_head ?? 'Head #' . $item->head_id,
                    'value' => 'revision:' . $item->id,
                    'label' => match ($batch->revision_type) {
                        'branch_promotion' => 'Branch Promotion',
                        'manual_fee_structure' => 'Manual Fee Structure',
                        'classwise_fee_structure' => 'Classwise Fee Structure',
                        'promotion' => 'Promotion',
                        default => ucwords(str_replace('_', ' ', (string) $batch->revision_type)),
                    },
                    'context' => trim(($batch->sessionFrom->year ?? '-') . ' to ' . ($batch->sessionTo->year ?? '-')),
                    'base_amount' => (float) ($item->new_base_amount ?? 0),
                    'payable_amount' => (float) ($item->new_payable_amount ?? 0),
                    'percentage' => (float) ($item->percentage ?? 0),
                    'effective_from' => $batch->effective_from,
                ]);
            }
        }

        $revisionOptionsByHead = $revisionOptionsByHead->groupBy('head_id');
        $challanHeadIds = $challan->heads->pluck('head_id');

        $activeConcession = $this->activeConcessionForStudent($challan->student_id, $challan->fee_month);
        $policyIds = collect([
            $challan->concession_id,
            optional($activeConcession)->concession_id,
        ])->filter()->unique()->values();
        $concessionPolicyTitles = $policyIds->isNotEmpty()
            ? ConcessionPolicy::whereIn('id', $policyIds)->pluck('title', 'id')
            : collect();
        $concessionPolicyOptions = collect(['' => 'No Concession Policy'])->union($concessionPolicyTitles);
        $selectedConcessionPolicyId = $request->input('concession_policy_id', $challan->concession_id ?: optional($activeConcession)->concession_id);
        $concessionPolicies = $selectedConcessionPolicyId
            ? ConcessionPolicyHead::where('concession_id', $selectedConcessionPolicyId)->pluck('percentage', 'head_id')
            : collect();
        $concessionPolicyPercentages = $policyIds->isNotEmpty()
            ? ConcessionPolicyHead::whereIn('concession_id', $policyIds)
                ->get()
                ->groupBy('concession_id')
                ->map(fn($rows) => $rows->pluck('percentage', 'head_id'))
                ->toArray()
            : [];

        $currentStructureOptionsByHead = StudentFeeStructure::with('feehead')
            ->where('reg_id', $challan->student_id)
            ->where('checked_status', 1)
            ->where(function ($query) use ($challan) {
                $query->where('branch_id', $challan->owned_by)
                    ->orWhere('owned_by', $challan->owned_by);
            })
            ->get()
            ->mapWithKeys(function ($structure) use ($concessionPolicies) {
                $baseAmount = (float) ($structure->amount ?? 0);
                $payableAmount = $this->payableAmountFromPolicy(
                    $baseAmount,
                    (int) $structure->head_id,
                    $concessionPolicies,
                    (float) ($structure->discount ?? 0)
                );

                return [
                    (int) $structure->head_id => [
                        'head_id' => (int) $structure->head_id,
                        'head_name' => $structure->feehead->fee_head ?? 'Head #' . $structure->head_id,
                        'value' => 'structure:' . $structure->head_id,
                        'label' => 'Current Student Structure',
                        'context' => 'Checked active fee structure',
                        'base_amount' => $baseAmount,
                        'payable_amount' => $payableAmount,
                        'percentage' => null,
                        'effective_from' => null,
                    ],
                ];
            });

        $extraChallanHeadIds = $currentStructureOptionsByHead->keys()
            ->diff($challanHeadIds)
            ->unique()
            ->values();

        $challanHeadRows = $challan->heads
            ->map(function ($challanHead) {
                $challanPayable = (float) (($challanHead->price ?? 0) - ($challanHead->concession ?? 0));

                return [
                    'head_id' => (int) $challanHead->head_id,
                    'head_name' => $challanHead->feeHead->fee_head ?? 'Head #' . $challanHead->head_id,
                    'is_challan_head' => true,
                    'checked' => true,
                    'challan_base' => (float) ($challanHead->price ?? 0),
                    'challan_payable' => $challanPayable,
                    'paid' => (float) ($challanHead->paid ?? 0),
                    'selected' => null,
                    'options' => collect(),
                ];
            })
            ->values()
            ->merge($extraChallanHeadIds->map(function ($headId) use ($currentStructureOptionsByHead) {
                $structureOption = $currentStructureOptionsByHead->get((int) $headId);

                return [
                    'head_id' => (int) $headId,
                    'head_name' => $structureOption['head_name'] ?? 'Head #' . $headId,
                    'is_challan_head' => false,
                    'checked' => false,
                    'challan_base' => (float) ($structureOption['base_amount'] ?? 0),
                    'challan_payable' => (float) ($structureOption['payable_amount'] ?? 0),
                    'paid' => 0,
                    'selected' => null,
                    'options' => collect([$structureOption])->filter()->values(),
                ];
            }))
            ->values();

        $extraHistoryHeadIds = $revisionOptionsByHead->keys()
            ->merge($currentStructureOptionsByHead->keys())
            ->diff($challanHeadIds)
            ->unique()
            ->values();

        $structureHistory = $challan->heads
            ->map(function ($challanHead) use ($revisionOptionsByHead) {
                $options = $revisionOptionsByHead->get((int) $challanHead->head_id, collect())->values();
                $challanPayable = (float) (($challanHead->price ?? 0) - ($challanHead->concession ?? 0));
                $matchedOption = $options->first(function ($option) use ($challanHead, $challanPayable) {
                    return round((float) $option['base_amount'], 2) === round((float) ($challanHead->price ?? 0), 2)
                        && round((float) $option['payable_amount'], 2) === round($challanPayable, 2);
                });
                $selected = $matchedOption['value'] ?? null;

                return [
                    'head_id' => (int) $challanHead->head_id,
                    'head_name' => $challanHead->feeHead->fee_head ?? 'Head #' . $challanHead->head_id,
                    'is_challan_head' => true,
                    'checked' => true,
                    'challan_base' => (float) ($challanHead->price ?? 0),
                    'challan_payable' => $challanPayable,
                    'paid' => (float) ($challanHead->paid ?? 0),
                    'selected' => $selected,
                    'options' => $options,
                ];
            })
            ->values()
            ->merge($extraHistoryHeadIds->map(function ($headId) use ($revisionOptionsByHead, $currentStructureOptionsByHead) {
                $options = $revisionOptionsByHead->get((int) $headId, collect())->values();
                $structureOption = $currentStructureOptionsByHead->get((int) $headId);

                if ($structureOption) {
                    $options = collect([$structureOption])->merge($options)->values();
                }

                $firstOption = $options->first();

                return [
                    'head_id' => (int) $headId,
                    'head_name' => $firstOption['head_name'] ?? 'Head #' . $headId,
                    'is_challan_head' => false,
                    'checked' => false,
                    'challan_base' => (float) ($structureOption['base_amount'] ?? 0),
                    'challan_payable' => (float) ($structureOption['payable_amount'] ?? 0),
                    'paid' => 0,
                    'selected' => null,
                    'options' => $options,
                ];
            }))
            ->values();

        $studentChallans = Challans::where('student_id', $challan->student_id)
            ->orderByDesc('fee_month')
            ->orderByDesc('id')
            ->get();

        $subscriptionLabel = $this->legacySubscriptionLabel($challan);

        return view('students.challanlists.legacy_show', compact(
            'challan',
            'student',
            'session',
            'sessions',
            'selectedSessionId',
            'concessionPolicyOptions',
            'selectedConcessionPolicyId',
            'concessionPolicyPercentages',
            'editMode',
            'canEdit',
            'canRollback',
            'challanHeadRows',
            'structureHistory',
            'studentChallans',
            'subscriptionLabel'
        ));
    }


    public function legacyUpdate($id, Request $request)
    {
        $challan = Challans::with('heads.feeHead')->findOrFail($id);

        if (Auth::user()->type !== 'company' || strtolower((string) $challan->status) !== 'issued') {
            return redirect()->route('challan.legacy_show', $challan->id)->with('error', 'Only company users can edit issued challans from this page.');
        }

        DB::beginTransaction();
        try {
            $challan->issue_date = $request->issue_date;
            $challan->due_date = $request->due_date;
            $challan->remarks = $request->remarks;
            $challan->session_id = $request->session_id ?: $challan->session_id;
            $challan->concession_id = $request->concession_policy_id ?: null;
            $subscriptionMap = [
                'monthly' => 1,
                'bi-monthly' => 2,
                'quarterly' => 3,
                '4-monthly' => 4,
                '5-monthly' => 5,
                '6-monthly' => 6,
                '7-monthly' => 7,
                '8-monthly' => 8,
                '9-monthly' => 9,
                '10-monthly' => 10,
                '11-monthly' => 11,
                'yearly' => 12,
            ];
            $duration = $subscriptionMap[$request->fee_subscription] ?? 1;

            if ($duration > 1) {
                $startDate = $challan->fee_month
                    ? Carbon::parse($challan->fee_month)
                    : Carbon::parse($challan->issue_date);
                $months = [];
                for ($i = 0; $i < $duration; $i++) {
                    $months[] = $startDate->copy()->addMonths($i)->format('Y-m-d');
                }
                $challan->other_months = implode(',', $months);
            } else {
                $challan->other_months = null;
            }
            $challan->save();

            $selected = $request->input('selected_revision', []);
            $checkedHeads = collect($request->input('checked_heads', []))->map(fn($headId) => (int) $headId)->unique()->values()->all();
            $existingBaseAmounts = $request->input('existing_base_amount', []);
            $existingPayableAmounts = $request->input('existing_payable_amount', []);
            $selectedConcessionPolicies = $challan->concession_id
                ? ConcessionPolicyHead::where('concession_id', $challan->concession_id)->pluck('percentage', 'head_id')
                : collect();

            $headsToRemove = ChallanHead::where('challan_id', $challan->id)
                ->whereNotIn('head_id', $checkedHeads)
                ->get();

            foreach ($headsToRemove as $head) {
                JournalItem::where('entry_id', $head->id)
                    ->where('types', 'Challan')
                    ->delete();
                $head->delete();
            }

            if (empty($checkedHeads)) {
                $challan->total_amount = 0;
                $challan->concession_amount = 0;
                $challan->paid_amount = 0;
                $challan->status = 'paid';
                $challan->save();

                DB::commit();

                return redirect()->route('challan.legacy_show', $challan->id)->with('success', 'Challan has been updated successfully.');
            }

            foreach ($checkedHeads as $headId) {
                $headId = (int) $headId;
                $choice = $selected[$headId] ?? null;
                [$source, $sourceId] = array_pad(explode(':', (string) $choice, 2), 2, null);

                if ($source === 'revision') {
                    $item = StudentFeeRevisionItem::where('id', $sourceId)
                        ->where('reg_id', $challan->student_id)
                        ->where('head_id', $headId)
                        ->first();

                    if (!$item) {
                        continue;
                    }

                    $unitGrossPrice = (float) $item->new_base_amount;
                    $unitPayableAmount = $this->payableAmountFromPolicy(
                        $unitGrossPrice,
                        $headId,
                        $selectedConcessionPolicies,
                        0
                    );
                } else {
                    $unitGrossPrice = isset($existingBaseAmounts[$headId])
                        ? (float) $existingBaseAmounts[$headId]
                        : null;
                    $unitPayableAmount = isset($existingPayableAmounts[$headId])
                        ? (float) $existingPayableAmounts[$headId]
                        : null;

                    if ($unitGrossPrice === null) {
                        continue;
                    }

                    $unitPayableAmount = $this->payableAmountFromPolicy(
                        $unitGrossPrice,
                        $headId,
                        $selectedConcessionPolicies,
                        0
                    );
                }

                $feeHead = FeeHead::find($headId);
                $headName = strtolower($feeHead->fee_head ?? '');
                $multiplier = (
                    str_contains($headName, 'admission fee') ||
                    str_contains($headName, 'annual fee') ||
                    str_contains($headName, 'late fee')
                ) ? 1 : $duration;

                $grossPrice = $unitGrossPrice * $multiplier;
                $payableAmount = $unitPayableAmount * $multiplier;
                $concessionAmount = max(0, $grossPrice - $payableAmount);

                $challanHead = ChallanHead::firstOrNew([
                    'challan_id' => $challan->id,
                    'head_id' => $headId,
                ]);
                $challanHead->price = $grossPrice;
                $challanHead->concession = $concessionAmount;
                $challanHead->paid = $challanHead->paid ?? 0;
                $challanHead->save();

                if ($feeHead) {
                    $this->syncLegacyChallanJournalHead($challan, $challanHead, $feeHead, $grossPrice, $concessionAmount);
                }
            }

            $challan->total_amount = ChallanHead::where('challan_id', $challan->id)->sum('price');
            $challan->concession_amount = ChallanHead::where('challan_id', $challan->id)->sum('concession');
            $challan->paid_amount = ChallanHead::where('challan_id', $challan->id)->sum('paid');
            $challan->save();

            DB::commit();

            return redirect()->route('challan.legacy_show', $challan->id)->with('success', 'Challan fee amounts have been updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('challan.legacy_show', ['id' => $challan->id, 'mode' => 'edit'])->with('error', $e->getMessage());
        }
    }

    public function legacyRollback($id)
    {
        $challan = Challans::findOrFail($id);

        if (!in_array(Auth::user()->type, ['company', 'super admin']) || strtolower((string) $challan->status) !== 'issued') {
            return redirect()->route('challan.legacy_show', $challan->id)->with('error', 'Only issued challans can be rolled back.');
        }

        DB::beginTransaction();
        try {
            if ($challan->voucher_id) {
                JournalItem::where('journal', $challan->voucher_id)->delete();
                JournalEntry::where('id', $challan->voucher_id)->where('voucher_type', 'JV')->delete();
            }

            ChallanHead::where('challan_id', $challan->id)->delete();
            $challanType = $challan->challan_type;
            $challan->delete();

            DB::commit();

            return redirect()->route($this->legacyChallanListRoute($challanType))->with('success', 'Challan rolled back successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('challan.legacy_show', $id)->with('error', $e->getMessage());
        }
    }

    private function activeConcessionForStudent($studentId, $asOfDate = null)
    {
        $date = $asOfDate ? Carbon::parse($asOfDate)->toDateString() : now()->toDateString();

        return Concession::where('student_id', $studentId)
            ->where('active_status', '!=', 0)
            ->where('status', 'Approved')
            ->where(function ($query) use ($date) {
                $query->whereDate('end_date', '>=', $date)
                    ->orWhereNull('end_date');
            })
            ->orderByDesc('id')
            ->first();
    }

    private function payableAmountFromPolicy(float $baseAmount, int $headId, $concessionPolicies, float $fallbackDiscount = 0): float
    {
        $percentage = $concessionPolicies->has($headId)
            ? (float) $concessionPolicies->get($headId)
            : $fallbackDiscount;

        $discount = round(($baseAmount * $percentage) / 100);

        return max(0, $baseAmount - $discount);
    }

    private function legacySubscriptionLabel(Challans $challan): string
    {
        $months = $challan->other_months
            ? array_values(array_filter(array_map('trim', explode(',', $challan->other_months))))
            : [];

        $count = max(1, count($months));

        return match ($count) {
            2 => 'Bi-Monthly Fee Subscription',
            3 => 'Quarterly Fee Subscription',
            12 => 'Annual Fee Subscription',
            default => $count === 1 ? 'Monthly Fee Subscription' : $count . ' Month Fee Subscription',
        };
    }

    private function syncLegacyChallanJournalHead(Challans $challan, ChallanHead $challanHead, FeeHead $feeHead, float $grossPrice, float $concessionAmount): void
    {
        $journalEntry = $challan->voucher_id
            ? JournalEntry::where('id', $challan->voucher_id)->where('voucher_type', 'JV')->first()
            : null;

        if (!$journalEntry) {
            $journalEntry = JournalEntry::where('reference_id', $challan->id)->where('voucher_type', 'JV')->first();
        }

        if (!$journalEntry) {
            return;
        }

        $student = $challan->student;
        $studentName = optional($student)->stdname ?: '';
        $rollNo = optional($student)->roll_no ?: ($challan->rollno ?? '');
        $branchName = optional($challan->branch)->name ?: '';
        $descriptionSuffix = ' Roll no ' . $rollNo . ' Challan no ' . $challan->challanNo . ' - ' . $studentName . ' - ' . $challan->fee_month . ' - ' . $branchName;

        $regularItems = JournalItem::where('entry_id', $challanHead->id)
            ->where('types', 'Challan')
            ->where(function ($query) {
                $query->where('is_discount', 0)->orWhereNull('is_discount');
            })
            ->get();

        if ($regularItems->isEmpty()) {
            if ($feeHead->account_id) {
                $income = new JournalItem;
                $income->entry_id = $challanHead->id;
                $income->types = 'Challan';
                $income->journal = $journalEntry->id;
                $income->head = $feeHead->id;
                $income->account = $feeHead->account_id;
                $income->description = 'Income Account:' . $descriptionSuffix;
                $income->credit = $grossPrice;
                $income->debit = 0;
                $income->user_type = 'Student';
                $income->user_id = $challan->student_id;
                $income->is_discount = 0;
                $income->save();
            }

            if ($feeHead->receivable_account_id) {
                $receivable = new JournalItem;
                $receivable->entry_id = $challanHead->id;
                $receivable->types = 'Challan';
                $receivable->journal = $journalEntry->id;
                $receivable->head = $feeHead->id;
                $receivable->account = $feeHead->receivable_account_id;
                $receivable->description = 'Account Receivable:' . $descriptionSuffix;
                $receivable->credit = 0;
                $receivable->debit = $grossPrice;
                $receivable->user_type = 'Student';
                $receivable->user_id = $challan->student_id;
                $receivable->is_discount = 0;
                $receivable->save();
            }
        } else {
            foreach ($regularItems as $item) {
                if ((float) $item->credit !== 0.0) {
                    $item->credit = $grossPrice;
                    $item->description = 'Income Account:' . $descriptionSuffix;
                } else {
                    $item->debit = $grossPrice;
                    $item->description = 'Account Receivable:' . $descriptionSuffix;
                }
                $item->user_type = 'Student';
                $item->user_id = $challan->student_id;
                $item->branch_id = $challan->owned_by;
                $item->save();
            }
        }

        $discountItems = JournalItem::where('entry_id', $challanHead->id)
            ->where('types', 'Challan')
            ->where('is_discount', 1)
            ->get();

        if ($concessionAmount <= 0) {
            foreach ($discountItems as $item) {
                $item->delete();
            }
            return;
        }

        if ($discountItems->isEmpty()) {
            if ($feeHead->discount_account_id) {
                $discountIncome = new JournalItem;
                $discountIncome->journal = $journalEntry->id;
                $discountIncome->account = $feeHead->discount_account_id;
                $discountIncome->head = $feeHead->id;
                $discountIncome->description = 'Discount Allowed Income:' . $descriptionSuffix;
                $discountIncome->user_id = $challan->student_id;
                $discountIncome->user_type = 'Student';
                $discountIncome->entry_id = $challanHead->id;
                $discountIncome->types = 'Challan';
                $discountIncome->is_discount = 1;
                $discountIncome->credit = 0;
                $discountIncome->debit = $concessionAmount;
                $discountIncome->save();
            }

            if ($feeHead->receivable_account_id) {
                $discountReceivable = new JournalItem;
                $discountReceivable->journal = $journalEntry->id;
                $discountReceivable->account = $feeHead->receivable_account_id;
                $discountReceivable->head = $feeHead->id;
                $discountReceivable->description = 'Discount Allowed Receivable:' . $descriptionSuffix;
                $discountReceivable->user_id = $challan->student_id;
                $discountReceivable->user_type = 'Student';
                $discountReceivable->entry_id = $challanHead->id;
                $discountReceivable->types = 'Challan';
                $discountReceivable->is_discount = 1;
                $discountReceivable->credit = $concessionAmount;
                $discountReceivable->debit = 0;
                $discountReceivable->save();
            }
            return;
        }

        foreach ($discountItems as $item) {
            if ((float) $item->credit !== 0.0) {
                $item->credit = $concessionAmount;
                $item->description = 'Discount Allowed Receivable:' . $descriptionSuffix;
            } else {
                $item->debit = $concessionAmount;
                $item->description = 'Discount Allowed Income:' . $descriptionSuffix;
            }
            $item->save();
        }
    }

    private function legacyChallanListRoute(?string $challanType): string
    {
        return match ($challanType) {
            'Admission' => 'admissionchallanlist',
            'ReAdmission' => 'readmissionchallanlist',
            'Registration' => 'registrationchallanlist',
            default => 'regularchallanlist',
        };
    }


    public function show($id, Request $request)
    {
        // dd($id, $request->all());
        $challan = Challans::where('id', '=', $id)->first();
        $challanhead = ChallanHead::where('challan_id', $challan->id)->get();
      $startDate = '2026-01-01';
        // $currentMonthStart = date('Y-m-01');
		        $currentMonthStart = $challan->fee_month;

        $previousUnpaidChallans = Challans::where('student_id', $challan->student_id)
            ->where('status', '!=', 'Paid')
            ->whereNotIn('challan_type', ['Registration','Withdrawal'])
            ->whereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') >= ?", [$startDate])
            ->whereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') < ?", [$currentMonthStart])
            ->where('id', '!=', $challan->id)
            ->get();
        $concessionData = Concession::with('concession', 'branches_address')->where('student_id', $challan->student_id)
            ->where('end_date', '>=', date('Y-m-d'))->orderBy('id', 'desc')->where('status', 'Approved')->first();
        if (!$concessionData) {
            $concessionData = Concession::with('concession', 'branches_address')->where('student_id', $challan->student_id)
                ->orderBy('id', 'desc')->whereNull('end_date')->where('status', 'Approved')->first();
        }
        $heads = [];
        $total = 0;
        $concession = 0;
        // Calculate concession for current challan heads
        foreach ($challanhead as $headId) {
            $head = FeeHead::findOrFail($headId->head_id);
            $student_fee_structure = StudentFeeStructure::where('reg_id', $challan->student_id)->where('head_id', $headId->head_id)->first();
            $concessionAmount = $headId->concession;
            // if ($concessionData) {
            //     $concessionHead = ConcessionPolicyHead::where('head_id', $headId->head_id)
            //         ->where('concession_id', $concessionData->concession_id)
            //         ->first();
            //     if ($concessionHead) {
            //         $concessionAmount = ($headId->price / 100) * $concessionHead->percentage;
            //     }
            // }
            $heads[] = [
                'name' => $head->fee_head,
                'amount' => @$student_fee_structure->amount ? (float) @$student_fee_structure->amount : 0,
                'headamount' => $headId->price ? (float) $headId->price : 0,
                'concession' => (float) $concessionAmount,
            ];
            $total += (float) ($headId->price ?? 0);
            $concession += (float) $concessionAmount;
        }
        // Calculate concession for previous unpaid challans
        foreach ($previousUnpaidChallans as $prevChallan) {
            foreach ($prevChallan->heads as $prevHead) {
                // dd($prevHead);
                $prevHead->concessionAmount = 0;
                // if ($concessionData) {
                //     $concessionHead = ConcessionPolicyHead::where('head_id', $prevHead->head_id)
                //         ->where('concession_id', $concessionData->concession_id)
                //         ->first();
                //     if ($concessionHead) {
                //         $prevHead->concessionAmount = ($prevHead->price / 100) * $concessionHead->percentage;
                //     }
                // }
            }
        }
        // dd($heads);
        $grandTotal = $total - $concession;
		$showJunJulExemptionLabel = $this->challanHasJunJulExemptionLabel($challan);
        if (isset($request->type) && $request->type != '') {
            // dd($challan, $heads, $previousUnpaidChallans, $grandTotal);

            $html = view('challans.challanPdf', compact('challan', 'heads', 'previousUnpaidChallans', 'grandTotal','showJunJulExemptionLabel'))->render();
            $options = new Options;
            $options->set('defaultFont', 'DejaVu Sans'); // good Unicode support
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $pdfContent = $dompdf->output();
            // stream pdf
            // dd($pdfContent);
            if ($request->type == 'print') {
                // dd($pdfContent);
                return $dompdf->stream('challan.pdf', ['Attachment' => false]);
            } else {
                return $dompdf->stream('challan.pdf');
            }
        }
        if($challan->challan_type == 'Registration') {
            return view('challans.regchallan', compact('challan', 'heads', 'previousUnpaidChallans', 'grandTotal'));
        }
        return view('challans.nchallan', compact('challan', 'heads', 'previousUnpaidChallans', 'grandTotal','showJunJulExemptionLabel'));
    }


    private function calculateAndUpdateLateFee($challan, $paymentDate, $receiveType = null)
    {
        // Skip if challan already paid
        if (strtolower($challan->status) == 'paid') {
            return;
        }

        // Apply late fee ONLY on regular challans
        if (strtolower(trim($challan->challan_type)) != 'regular' && strtolower(trim($challan->challan_type)) != 'advance') {
            return;
        }

        $dueDate = \Carbon\Carbon::parse($challan->due_date);
        $today = \Carbon\Carbon::parse($paymentDate);

        // No late fee before/on due date
        if ($today->lte($dueDate)) {
            return;
        }

        // Days overdue
        $daysOverdue = $today->diffInDays($dueDate);

        // OL payment type grace rule
        // Exactly 1 day late => exempt
        if (strtoupper($receiveType) == 'OL' && $daysOverdue == 1) {
            return;
        }

        // 50% payment exemption
        $totalPayable = $challan->total_amount - ($challan->concession_amount ?? 0);

        if ($totalPayable > 0) {

            $alreadyPaid = $challan->paid_amount ?? 0;

            // If already crossed 50%, no late fee
            if ($alreadyPaid >= ($totalPayable * 0.5)) {
                return;
            }
        }

        // Daily late fee
        $lateFeePerDay = 120;

        // Calculate fee
        $lateFeeAmount = $daysOverdue * $lateFeePerDay;

        // Maximum cap = 1200
        $lateFeeAmount = min($lateFeeAmount, 1200);

        // Find late fee head
        $lateFeeHead = FeeHead::where('fee_head', 'LATE FEE')->first();

        if (!$lateFeeHead) {
            return;
        }

        // Existing late fee
        $existingLateFee = ChallanHead::where('challan_id', $challan->id)
            ->where('head_id', $lateFeeHead->id)
            ->first();

        if ($existingLateFee) {

            // Remove old fee first
            $challan->total_amount -= $existingLateFee->price;

            // Update challan head
            $existingLateFee->update([
                'price' => $lateFeeAmount,
                'updated_at' => now(),
            ]);

            // Income entry
            $journalItem = JournalItem::where('journal', $challan->voucher_id)
                ->where('head', $lateFeeHead->id)
                ->where('debit', 0)
                ->first();

            if ($journalItem) {
                $journalItem->update([
                    'credit' => $lateFeeAmount,
                    'updated_at' => now(),
                ]);
            }

            // Receivable entry
            $journalItem = JournalItem::where('journal', $challan->voucher_id)
                ->where('head', $lateFeeHead->id)
                ->where('credit', 0)
                ->first();

            if ($journalItem) {
                $journalItem->update([
                    'debit' => $lateFeeAmount,
                    'updated_at' => now(),
                ]);
            }

            // Add updated fee
            $challan->total_amount += $lateFeeAmount;
            $challan->save();

        } else {

            // Create challan head
            $latehead = ChallanHead::create([
                'challan_id' => $challan->id,
                'head_id' => $lateFeeHead->id,
                'price' => $lateFeeAmount,
                'concession' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($latehead) {

                $feeHeads = FeeHead::find($lateFeeHead->id);

                // Income entry
                $journalItem = new JournalItem;
                $journalItem->journal = $challan->voucher_id;
                $journalItem->account = $feeHeads->account_id;
                $journalItem->head = $lateFeeHead->id;
                $journalItem->entry_id = $latehead->id;
                $journalItem->user_id = $challan->student_id;
                $journalItem->user_type = 'Student';
                $journalItem->description =
                    'Income Account: Roll no ' .
                    $challan->student->roll_no .
                    ' Challan no ' .
                    $challan->challanNo;

                $journalItem->types = 'Challan';
                $journalItem->credit = $lateFeeAmount;
                $journalItem->debit = 0;
                $journalItem->created_at = $challan->created_at;
                $journalItem->updated_at = $challan->updated_at;
                $journalItem->save();

                // Receivable entry
                $journalItem = new JournalItem;
                $journalItem->journal = $challan->voucher_id;
                $journalItem->account = $feeHeads->receivable_account_id;
                $journalItem->head = $lateFeeHead->id;
                $journalItem->entry_id = $latehead->id;
                $journalItem->user_id = $challan->student_id;
                $journalItem->user_type = 'Student';
                $journalItem->description =
                    'Account Receivable: Roll no ' .
                    $challan->student->roll_no .
                    ' Challan no ' .
                    $challan->challanNo;

                $journalItem->types = 'Challan';
                $journalItem->credit = 0;
                $journalItem->debit = $lateFeeAmount;
                $journalItem->created_at = $challan->created_at;
                $journalItem->updated_at = $challan->updated_at;
                $journalItem->save();
            }

            $challan->total_amount += $lateFeeAmount;
            $challan->save();
        }
    }

    public function downloadCardAsPDF()
    {
        $html = view('challan')->renderSections()['content'];
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->stream('card.pdf');
    }

    public function fetchClassId(Request $request)
    {
        $studentId = $request->input('id');
        $student = StudentRegistration::findOrFail($studentId);
        $classId = $student->classname;

        return response()->json(['class_id' => $classId]);
    }

    public function fetchHeadsAndAmounts(Request $request)
    {
        // dd('hi');
        $classId = $request->input('class_id');
        $classWiseFees = ClassWiseFee::where('class_id', $classId)
            ->join('fee_heads', 'class_wise_fees.head_id', '=', 'fee_heads.id')
            ->select('class_wise_fees.head_id', 'fee_heads.fee_head as head_name', 'class_wise_fees.amount')
            ->get();

        $data = [];
        foreach ($classWiseFees as $fee) {
            $data[] = [
                'head_id' => $fee->head_id,
                'head_name' => $fee->head_name,
                'amount' => $fee->amount,
            ];
        }

        return response()->json($data);
    }

    public function challanpay(Request $request, $id)
    {

        $challan = Challans::findOrFail($id);
        if (\Auth::user()->type == 'company') {
            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        } else {
            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        }

        return view('challans.paychallan', compact('id', 'challan', 'accounts'));
    }

    public function challanpaid(Request $request, $id)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'date' => 'required',
                'amount' => 'required',
                'account_id' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        \DB::beginTransaction();
        $data = [];
        try {
            $invoicePayment = Challans::findOrFail($id);
            $invoicePayment->paid_date = $request->date;
            $invoicePayment->paid_amount = $request->amount;
            if (! empty($request->add_receipt) && $request->hasFile('add_receipt')) {
                // storage limit
                $image_size = $request->file('add_receipt')->getSize();
                $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
                if ($result == 1) {
                    $fileName = time().'_'.$request->add_receipt->getClientOriginalName();
                    $request->add_receipt->storeAs('uploads/payment', $fileName);
                    $invoicePayment->add_receipt = $fileName;
                }
            }
            $invoicePayment->save();
            $challan = Challans::findOrFail($id);
            $paidamount = $challan->paid_amount;
            $totalamount = $challan->total_amount;
            $concessionamount = $challan->concession_amount;
            $dueamount = $totalamount - ($paidamount + $concessionamount);
            if ($dueamount == 0) {
                $challan->status = 'Paid';
                $challan->save();
            } elseif ($dueamount < $totalamount) {
                $challan->status = 'Partial Paid';
                $challan->save();
            } else {
                $challan->status = 'Issued';
                $challan->save();
            }
            Utility::bankAccountBalance($request->account_id, $request->amount, 'credit');

            // dd($invoicePayment);
            // dd($challan);
            $bankAccount = BankAccount::find($request->account_id);
            $data['id'] = $id;
            $data['date'] = $invoicePayment->date;
            $data['reference'] = $invoicePayment->reference;
            $data['description'] = $invoicePayment->description;
            $data['amount'] = $invoicePayment->amount;
            $data['category'] = $invoicePayment->challan_type;
            $data['owned_by'] = $invoicePayment->owned_by;
            $data['created_by'] = $invoicePayment->created_by;
            $data['account_id'] = $bankAccount->chart_account_id;

            // if(ucwords($bankAccount->bank_name) == 'Cash' || ucwords($bankAccount->holder_name) == 'Cash'){
            //     $dataret  = Utility::crv_entry($data);
            // }else{
            //     $dataret  = Utility::brv_entry($data);
            // }
            \DB::commit();

            return redirect()->route('challan.index')->with('Installment has been created successfully');

            return redirect()->back()->with('success', __('Payment successfully added.'));
            // return redirect()->back()->with('success', __('Payment successfully added.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : '') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        } catch (\Exception $e) {
            \DB::rollback();
            dd($e);

            return redirect()->back()->with('error', $e);
        }
    }


   
    public function calculateLateFeeAjax($id, Request $request)
    {
        $challan = Challans::findOrFail($id);

        if (Auth::user()->type !== 'company') {
            return response()->json(['error' => 'Only company users can calculate late fee.'], 403);
        }

        if (strtolower((string) $challan->status) !== 'issued') {
            return response()->json(['error' => 'Late fee can only be calculated on issued challans.'], 400);
        }

        $request->validate([
            'late_until' => 'required|date',
        ]);

        $dueDate = Carbon::parse($challan->due_date);
        $lateUntil = Carbon::parse($request->late_until);

        if ($lateUntil->lte($dueDate)) {
            return response()->json(['error' => 'Late until date must be after the due date (' . $dueDate->format('Y-m-d') . ').'], 400);
        }

        if ($lateUntil->gt($dueDate->copy()->addDays(10))) {
            return response()->json(['error' => 'Late until date cannot exceed 10 days after the due date (' . $dueDate->format('Y-m-d') . ').'], 400);
        }

        $lateFeeHead = FeeHead::where('fee_head', 'LIKE', '%LATE FEE%')->first();
        if (!$lateFeeHead) {
            return response()->json(['error' => 'No LATE FEE head found. Please create a fee head named "LATE FEE".'], 400);
        }

        $daysOverdue = $dueDate->diffInDays($lateUntil);
        $lateFeePerDay = 120;
        $lateFeeAmount = $daysOverdue * $lateFeePerDay;
        $lateFeeAmount = min($lateFeeAmount, 1200);

        $existingLateFee = ChallanHead::where('challan_id', $challan->id)
            ->where('head_id', $lateFeeHead->id)
            ->first();

        return response()->json([
            'days_overdue' => $daysOverdue,
            'late_fee_per_day' => $lateFeePerDay,
            'late_fee_amount' => $lateFeeAmount,
            'max_cap' => 1200,
            'existing' => $existingLateFee ? (float) $existingLateFee->price : null,
            'head_id' => $lateFeeHead->id,
            'head_name' => $lateFeeHead->fee_head,
        ]);
    }

    public function applyLateFee($id, Request $request)
    {
        $challan = Challans::with('heads.feeHead')->findOrFail($id);

        if (Auth::user()->type !== 'company') {
            return redirect()->route('challan.legacy_show', $challan->id)->with('error', 'Only company users can apply late fee.');
        }

        if (strtolower((string) $challan->status) !== 'issued') {
            return redirect()->route('challan.legacy_show', $challan->id)->with('error', 'Late fee can only be applied on issued challans.');
        }

        $request->validate([
            'late_until' => 'required|date',
        ]);

        $dueDate = Carbon::parse($challan->due_date);
        $lateUntil = Carbon::parse($request->late_until);

        if ($lateUntil->lte($dueDate)) {
            return redirect()->route('challan.legacy_show', $challan->id)->with('error', 'Late until date must be after the due date.');
        }

        if ($lateUntil->gt($dueDate->copy()->addDays(10))) {
            return redirect()->route('challan.legacy_show', $challan->id)->with('error', 'Late until date cannot exceed 10 days after the due date.');
        }

        if (!str_contains(strtolower($challan->challan_type), 'regular') && !str_contains(strtolower($challan->challan_type), 'advance')) {
            return redirect()->route('challan.legacy_show', $challan->id)->with('error', 'Late fee only applies to Regular and Advance challans.');
        }

        $lateFeeHead = FeeHead::where('fee_head', 'LIKE', '%LATE FEE%')->first();
        if (!$lateFeeHead) {
            return redirect()->route('challan.legacy_show', $challan->id)->with('error', 'No LATE FEE head found. Please create a fee head named "LATE FEE".');
        }

        DB::beginTransaction();
        try {
            $daysOverdue = $dueDate->diffInDays($lateUntil);
            $lateFeeAmount = min($daysOverdue * 120, 1200);

            $existingLateFee = ChallanHead::where('challan_id', $challan->id)
                ->where('head_id', $lateFeeHead->id)
                ->first();

            if ($existingLateFee) {
                $oldPrice = $existingLateFee->price;
                $difference = $lateFeeAmount - $oldPrice;

                $existingLateFee->update([
                    'price' => $lateFeeAmount,
                    'updated_at' => now(),
                ]);

                $journalItems = JournalItem::where('entry_id', $existingLateFee->id)
                    ->where('types', 'Challan')
                    ->get();

                foreach ($journalItems as $item) {
                    $item->journal = $challan->voucher_id;
                    if ((float) $item->credit !== 0.0) {
                        $item->credit = $lateFeeAmount;
                    } else {
                        $item->debit = $lateFeeAmount;
                    }
                    $item->save();
                }

                $challan->total_amount += $difference;
                $challan->save();
            } else {
                $latehead = ChallanHead::create([
                    'challan_id' => $challan->id,
                    'head_id' => $lateFeeHead->id,
                    'price' => $lateFeeAmount,
                    'concession' => 0,
                    'paid' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $student = $challan->student;
                $rollNo = optional($student)->roll_no ?: ($challan->rollno ?? '');
                $description = 'Roll no ' . $rollNo . ' Challan no ' . $challan->challanNo;

                if ($lateFeeHead->account_id) {
                    $income = new JournalItem;
                    $income->entry_id = $latehead->id;
                    $income->types = 'Challan';
                    $income->journal = $challan->voucher_id;
                    $income->head = $lateFeeHead->id;
                    $income->account = $lateFeeHead->account_id;
                    $income->description = 'Income Account: ' . $description;
                    $income->credit = $lateFeeAmount;
                    $income->debit = 0;
                    $income->user_type = 'Student';
                    $income->user_id = $challan->student_id;
                    $income->is_discount = 0;
                    $income->save();
                }

                if ($lateFeeHead->receivable_account_id) {
                    $receivable = new JournalItem;
                    $receivable->entry_id = $latehead->id;
                    $receivable->types = 'Challan';
                    $receivable->journal = $challan->voucher_id;
                    $receivable->head = $lateFeeHead->id;
                    $receivable->account = $lateFeeHead->receivable_account_id;
                    $receivable->description = 'Account Receivable: ' . $description;
                    $receivable->credit = 0;
                    $receivable->debit = $lateFeeAmount;
                    $receivable->user_type = 'Student';
                    $receivable->user_id = $challan->student_id;
                    $receivable->is_discount = 0;
                    $receivable->save();
                }

                $challan->total_amount += $lateFeeAmount;
                $challan->save();
            }

            DB::commit();

            return redirect()->route('challan.legacy_show', $challan->id)->with('success', 'Late fee of ' . number_format($lateFeeAmount, 2) . ' has been applied (Days overdue: ' . $daysOverdue . ').');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('challan.legacy_show', $challan->id)->with('error', 'Failed to apply late fee: ' . $e->getMessage());
        }
    }

    public function generateChallan(Request $request)
    {
        $checkedRowsData = $request->input('checkedRowsData');
        $existingChallanStudent = Challans::where('student_id', $request->input('student_id'))
            ->where('challan_type', 'Admission')->exists();
        if ($existingChallanStudent) {
            return response()->json(['error' => true, 'message' => 'Admission Challan already been generated.', 'code' => 422], 422);
        }
        // $validatedData = $request->validate([
        //     'student_id' => 'required',
        //     'challanDate' => 'required|date',
        //     'issueDate' => 'required|date',
        //     'dueDate' => 'required|date',
        // ]);

        DB::beginTransaction();
        try {
            $total = 0;
            $concession = 0;
            $item = [];
            $session = Session::orderBy('id', 'Desc')->where('active_status', '1')->where('created_by', '=', \Auth::user()->creatorId())->first();
            $student = StudentRegistration::where('id', $request->input('student_id'))->first();

            $challan = new Challans;
            $challan->student_id = $request->input('student_id');
            $challan->class_id = $student->class_id;
            $challan->rollno = $student->roll_no;
            $challan->challanNo = $this->challanNo();
            $challan->fee_month = date('Y-m-01', strtotime($request->input('challanDate')));
            $challan->challan_date = date('Y-m-d', strtotime($request->input('challanDate')));
            $challan->challan_type = 'Admission';
            $challan->total_amount = $total;
            $challan->issue_date = $request->input('issueDate');
            $challan->due_date = $request->input('dueDate');
            $challan->status = 'Issued';
            $challan->session_id = $session->id;
            $challan->owned_by = $student->owned_by;
            $challan->created_by = $student->created_by;
            $challan->save();

            $totalAmount = 0;
            $itemIndex = 0;
            $concessiondata = Concession::where('student_id', $request->student_id)->where('end_date', '>=', date('Y-m-d'))->orderBy('id', 'Desc')->where('status', 'Approved')->first();
            if (! $concessiondata) {
                $concessiondata = Concession::with('concession')->where('student_id', $request->student_id)
                    ->orderBy('id', 'desc')->whereNull('end_date')->where('status', 'Approved')->first();
            }

            foreach ($checkedRowsData as $row) {
                if (strpos(strtolower($row[0]), 'registration') !== false || strpos(strtolower($row[0]), 'transfer') !== false || strpos(strtolower($row[0]), 're-admission') !== false) {
                    continue;
                }
                $totalAmount += $row[3];
                if ($concessiondata) {
                    $conession_head = ConcessionPolicyHead::where('head_id', $row[5])->where('concession_id', $concessiondata->concession_id)->first();
                    if ($conession_head) {
                        $concessionAmount = round(($row[1] / 100) * $conession_head->percentage);
                    } else {
                        $concessionAmount = round(($row[1] / 100) * $row[2]);
                    }
                } else {
                    $concessionAmount = round(($row[1] / 100) * $row[2]);
                }
                $challan_head = new ChallanHead;
                $challan_head->challan_id = $challan->id;
                $challan_head->head_id = $row[5];
                $challan_head->price = $row[1] ? $row[1] : 0;
                $challan_head->concession = @$concessionAmount ? @$concessionAmount : 0;
                $challan_head->save();

                $total += $row[1];
                $concession += @$concessionAmount ? @$concessionAmount : 0;
                $item[$itemIndex]['pord_id'] = $challan_head->id;
                $item[$itemIndex]['head'] = $row[5];
                $item[$itemIndex]['price'] = $row[1] ? $row[1] : 0;
                $item[$itemIndex]['quantity'] = 1;
                $item[$itemIndex]['concession'] = @$concessionAmount ? @$concessionAmount : 0;
                $item[$itemIndex]['total'] = $total;
                $itemIndex++;
            }

            $challan->concession_id = @$concessiondata ? @$concessiondata->concession_id : '';
            $challan->concession_amount = @$concession ? @$concession : 0;
            $challan->total_amount = $total;
            $challan->save();

            $data['id'] = $challan->id;
            $data['no'] = $challan->challanNo;
            $data['date'] = $challan->challan_date;
            $data['reference'] = $challan->student_id;
            $data['category'] = 'Admission';
            $data['user_id'] = $challan->student_id;
            $data['std_name'] = $student->stdname;
            $data['branch_name'] = $student->branches->name;
            $data['fee_month'] = $challan->fee_month;
            $data['bank_name'] = '';
            $data['user_type'] = 'Student';
            $data['owned_by'] = $challan->owned_by;
            $data['created_by'] = $challan->created_by;
            $data['items'] = $item;

            // dd($data);
            $dataret = Utility::jrentry($data);
            $challan->voucher_id = $dataret;
            $challan->save();

            DB::commit();

            return response()->json(['success' => true, 'data' => $challan]);

            return redirect()->back()->with('Challan has been created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);

            return redirect()->back()->with('error', $e);
        }
    }

    public function challanNo()
    {
        $latest = Challans::orderByRaw('CAST(challanNo AS UNSIGNED) DESC')
            ->first();

        if (! $latest || ! $latest->challanNo) {
            return 1;
        }

        return (int) $latest->challanNo + 1;
    }

       public function printChallans(Request $request)
    {
        try {
            $challanIds = $request->input('rowsdata');
            $printType = $request->input('printType');

            if (!is_array($challanIds)) {
                return response()->json(['error' => 'Invalid data format'], 400);
            }

            if (empty($challanIds)) {
                return response()->json(['error' => 'No challans selected'], 400);
            }

            $maxChallans = 1500;
            if (count($challanIds) > $maxChallans) {
                return response()->json([
                    'error' => 'Maximum ' . $maxChallans . ' challans can be printed at once. You selected ' . count($challanIds) . '.'
                ], 400);
            }

            $batchSize = $request->input('batchSize', 100);
            $batchIndex = $request->input('batchIndex', 0);

            // ================= SINGLE PDF =================
            if ($printType === 'single') {
                $pdfContentsArray = [];

                foreach ($challanIds as $challanId) {
                    $challan = Challans::findOrFail($challanId);
                    $studentId = $challan->student_id;
                    $previousUnpaidChallans = Challans::where('student_id', $studentId)
                        ->whereNotIn('challan_type', ['Registration','Withdrawal'])
                        ->whereRaw("LOWER(status) != 'paid'")
                        ->where('id', '!=', $challan->id)
                        ->whereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') >= '2026-01-01'")
                        ->whereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') < STR_TO_DATE(?, '%Y-%m-%d')", [$challan->fee_month])
                        ->get();
                    $challanHeads = ChallanHead::where('challan_id', $challanId)->get();
                    $heads = [];

                    foreach ($challanHeads as $headItem) {
                        $head = FeeHead::findOrFail($headItem->head_id);

                        $studentFeeStructure = StudentFeeStructure::where('reg_id', $studentId)
                            ->where('head_id', $headItem->head_id)
                            ->first();

                        $heads[] = [
                            'name' => $head->fee_head,
                            'amount' => $studentFeeStructure?->amount ? (float) $studentFeeStructure->amount : 0,
                            'headamount' => $headItem->price ? (float) $headItem->price : 0,
                            'concession' => (float) $headItem->concession,
                        ];
                    }

                    $pdfContentsArray[] = $this->generateChallanPDF(
                        'challans.printchallan',
                        [
                            'challan' => $challan,
                            'heads' => $heads,
                            'previousUnpaidChallans' => $previousUnpaidChallans,
							'showJunJulExemptionLabel' => $this->challanHasJunJulExemptionLabel($challan),
                        ]
                    );
                }

                $mergedPdfContent = $this->mergePdfs($pdfContentsArray);

                return response()->json([
                    'pdfs' => [base64_encode($mergedPdfContent)],
                    'processedCount' => count($challanIds),
                    'totalCount' => count($challanIds),
                    'hasMoreBatches' => false,
                    'printType' => 'single',
                    'message' => 'Processing ' . count($challanIds) . '/' . count($challanIds) . ' challans...'
                ]);
            }

            // ================= SEPARATE PDF =================
            $totalBatches = ceil(count($challanIds) / $batchSize);
            $startIndex = $batchIndex * $batchSize;
            $endIndex = min($startIndex + $batchSize, count($challanIds));
            $currentBatchIds = array_slice($challanIds, $startIndex, $endIndex - $startIndex);

            $pdfContentsArray = [];

            foreach ($currentBatchIds as $challanId) {
                $challan = Challans::findOrFail($challanId);
                $studentId = $challan->student_id;

                // ✅ FIXED ARREARS LOGIC
                $previousUnpaidChallans = Challans::where('student_id', $studentId)
                    ->whereRaw("LOWER(status) != 'paid'")
                    ->whereNotIn('challan_type', ['Registration'])
                    ->where('id', '!=', $challan->id)
                    ->whereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') >= '2026-01-01'")
                    ->whereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') < STR_TO_DATE(?, '%Y-%m-%d')", [$challan->fee_month])
                    ->get();

                $challanHeads = ChallanHead::where('challan_id', $challanId)->get();
                $heads = [];

                foreach ($challanHeads as $headItem) {
                    $head = FeeHead::findOrFail($headItem->head_id);

                    $studentFeeStructure = StudentFeeStructure::where('reg_id', $studentId)
                        ->where('head_id', $headItem->head_id)
                        ->first();

                    $heads[] = [
                        'name' => $head->fee_head,
                        'amount' => $studentFeeStructure?->amount ? (float) $studentFeeStructure->amount : 0,
                        'headamount' => $headItem->price ? (float) $headItem->price : 0,
                        'concession' => (float) $headItem->concession,
                    ];
                }

                $pdfContentsArray[] = $this->generateChallanPDF(
                    'challans.printchallan',
                    [
                        'challan' => $challan,
                        'heads' => $heads,
                        'previousUnpaidChallans' => $previousUnpaidChallans,
						'showJunJulExemptionLabel' => $this->challanHasJunJulExemptionLabel($challan),
                    ]
                );
            }

            $hasMoreBatches = ($batchIndex < $totalBatches - 1);

            return response()->json([
                'pdfs' => array_map('base64_encode', $pdfContentsArray),
                'batchIndex' => $batchIndex,
                'totalBatches' => $totalBatches,
                'processedCount' => $endIndex,
                'totalCount' => count($challanIds),
                'hasMoreBatches' => $hasMoreBatches,
                'printType' => 'separate',
                'message' => 'Downloading ' . $endIndex . '/' . count($challanIds) . ' challans...'
            ]);

        } catch (\Exception $e) {
            \Log::error('Print Challans Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateChallanPDF($viewName, $data, $request = null)
    {
        // Use simpler dompdf settings like the existing code
        $html = view($viewName, $data)->render();
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A3', 'landscape');
        $dompdf->render();

        // If request is provided and has type parameter, stream the PDF
        if ($request && isset($request->type) && $request->type != '') {
            if ($request->type == 'print') {
                return $dompdf->stream('challan.pdf', ['Attachment' => false]);
            } else {
                return $dompdf->stream('challan.pdf');
            }
        }

        // Otherwise return PDF content for further processing
        return $dompdf->output();
    }

    private function mergePdfs(array $pdfContentsArray)
    {
        $pdf = new FPDI;

        // Use custom dimensions that match your challan template for proper fitting
        // These dimensions ensure the challan fits the whole page properly
        $customWidth = 1190.89;   // Custom width for challan
        $customHeight = 841.89;   // Custom height for challan

        foreach ($pdfContentsArray as $index => $pdfContent) {
            try {
                $pageCount = $pdf->setSourceFile(StreamReader::createByString($pdfContent));

                // Add each page of the current student's challan
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);

                    // Add new page for each student's challan with custom dimensions
                    $pdf->AddPage('L', [$customWidth, $customHeight]);

                    // Get the size of the imported page
                    $size = $pdf->getTemplateSize($templateId);

                    // Scale the template to fit the whole page
                    $scaleX = $customWidth / $size['width'];
                    $scaleY = $customHeight / $size['height'];
                    $scale = min($scaleX, $scaleY); // Use the smaller scale to maintain aspect ratio

                    // Center the content on the page
                    $x = ($customWidth - ($size['width'] * $scale)) / 2;
                    $y = ($customHeight - ($size['height'] * $scale)) / 2;

                    // Use template with scaling and positioning to fit whole page
                    $pdf->useTemplate($templateId, $x, $y, $size['width'] * $scale, $size['height'] * $scale);
                }

            } catch (\Exception $e) {
                // Log error but continue with other PDFs
                \Log::error('Error merging PDF for student ' . ($index + 1) . ': ' . $e->getMessage());

                continue;
            }
        }

        return $pdf->Output('S');
    }


    public function challandata_for_receipt(Request $request)
{
    try {
        $challandata = Challans::with(['student', 'heads.feeHead'])->where('challanNo', $request->challan_id)->first();
        
        if (!$challandata) {
            return response()->json(['error' => 'Challan not found.'], 404);
        }

        if ($challandata && $challandata->heads) {
            $headsData = [];
            foreach ($challandata->heads as $head) {
                $price = (float) ($head->price ?? 0);
                $concession = (float) ($head->concession ?? 0);
                $paid = (float) ($head->paid ?? 0);
                $amount = $price - $concession - $paid;

                if ($amount != 0) {
                    $headsData[] = [
                        'head_id' => $head->head_id ?? '',
                        'head_name' => $head->feeHead->fee_head ?? 'No FeeHead Name',
                        'amount' => $amount,
                    ];
                }
            }
        } else {
            $headsData = [];
        }

        $previousUnpaidChallans = Challans::with('heads.feeHead')
          
            ->where('student_id', $challandata->student_id)
            ->where('status', '!=', 'Paid')
            ->where('id', '!=', $challandata->id)
            ->wheredate('fee_month', '<', date('Y-m-01', strtotime($challandata->fee_month)))
            ->get();

        // Get bank accounts with chart_of_account information
        $defaultBankId = null;
        
        if (Auth::user()->type == 'company') {
            $account_all = BankAccount::select('id', 'bank_name', 'holder_name', 'chart_account_id')
                ->with('chartAccount:id,name')
                ->where('created_by', \Auth::user()->creatorId())
                ->get();
                
            $accounts = BankAccount::select('id', 'bank_name', 'holder_name', 'chart_account_id')
                ->with('chartAccount:id,name')
                ->where('owned_by', Auth::user()->ownedId())
                ->get();
        } else {
            // For branch users, get their owned account as default
            $branchAccount = BankAccount::where('owned_by', \Auth::user()->ownedId())->first();
            $defaultBankId = $branchAccount ? $branchAccount->id : null;
            
            if ($challandata->owned_by != Auth::user()->ownedId()) {
                $accounts = BankAccount::select('id', 'bank_name', 'holder_name', 'chart_account_id')
                    ->with('chartAccount:id,name')
                    ->where('owned_by', \Auth::user()->ownedId())
                    ->get();

                $account_all = BankAccount::select('id', 'bank_name', 'holder_name', 'chart_account_id')
                    ->with('chartAccount:id,name')
                    ->where('created_by', \Auth::user()->creatorId())
                    ->get();
            } else {
                $accounts = BankAccount::select('id', 'bank_name', 'holder_name', 'chart_account_id')
                    ->with('chartAccount:id,name')
                    ->where('owned_by', \Auth::user()->ownedId())
                    ->get();

                $account_all = BankAccount::select('id', 'bank_name', 'holder_name', 'chart_account_id')
                    ->with('chartAccount:id,name')
                    ->where('created_by', \Auth::user()->creatorId())
                    ->get();
            }
        }

        // Format accounts for select dropdown with chart account info
        $accountsFormatted = [];
        $accountsData = [];
        foreach ($accounts as $account) {
            $accountsFormatted[$account->id] = $account->bank_name . ' ' . $account->holder_name;
            $accountsData[$account->id] = [
                'name' => $account->bank_name . ' ' . $account->holder_name,
                'chart_account' => $account->chartAccount ? strtolower($account->chartAccount->name) : ''
            ];
        }
        
        $accountAllFormatted = [];
        $accountAllData = [];
        foreach ($account_all as $account) {
            $accountAllFormatted[$account->id] = $account->bank_name . ' ' . $account->holder_name;
            $accountAllData[$account->id] = [
                'name' => $account->bank_name . ' ' . $account->holder_name,
                'chart_account' => $account->chartAccount ? strtolower($account->chartAccount->name) : ''
            ];
        }

        return response()->json([
            'challandetail' => $challandata,
            'previousUnpaidChallans' => $previousUnpaidChallans,
            'headsData' => $headsData,
            'accounts' => $accountsFormatted,
            'account_all' => $accountAllFormatted,
            'accounts_data' => $accountsData,
            'account_all_data' => $accountAllData,
            'default_bank_id' => $defaultBankId,
        ]);

    } catch (\Exception $e) {
        \Log::error('Error fetching challan data: '.$e->getMessage());
        return response()->json(['error' => 'An error occurred while fetching challan data.'], 500);
    }
}

    // public function bulkchallan(Request $request)
    // {
    //     // dd($request->all());

    //     \DB::beginTransaction();
    //     try {
    //         $students = [];
    //         $gen_count = 0;
    //         $challanDate = Carbon::parse($request->challan_date);
    //         $dueDat = Carbon::parse($request->challan_date);
    //         $fee_month = Carbon::parse($request->challan_date);
    //         // Get the 26th of the previous month
    //         $issueDate = $challanDate->subMonthNoOverflow()->day(26)->toDateString();
    //         // $issueDate = $challanDate->startOfMonth()->toDateString();
    //         // $issueDate = Carbon::now()->toDateString();
    //         // $dueDate = Carbon::parse($issueDate)->addDays(6);
    //         // // If the due date is a Sunday, add 1 more day to make it Monday
    //         // if ($dueDate->isSunday()) {
    //         //     $dueDate->addDay();
    //         // }
    //         // $dueDate = $dueDate->toDateString();
    //         $dueDate = $dueDat->copy()->day(8);

    //         // If the due date is Sunday, move to Monday
    //         if ($dueDate->isSunday()) {
    //             $dueDate->addDay();
    //         }
    //         $dueDate = $dueDate->toDateString();

    //         $year = $fee_month->year;
    //         $month = $fee_month->month;
    //         if ($request->input('student') == 'all') {
    //             $validator = \Validator::make(
    //                 $request->all(),
    //                 [
    //                     'branches' => 'required',
    //                     'session' => 'required',
    //                     'class' => 'required',
    //                     'challan_date' => 'required',
    //                 ]
    //             );

    //             if ($validator->fails()) {
    //                 // Get the first error message
    //                 $messages = $validator->getMessageBag();

    //                 return response()->json([
    //                     'success' => false,
    //                     'error' => $messages->first(),
    //                 ], 422);
    //             }
    //             $query = StudentRegistration::where('owned_by', $request->branches)->where('student_status', 'Enrolled');
    //         } else {
    //             // dd($request->all());
    //             $validator = \Validator::make(
    //                 $request->all(),
    //                 [
    //                     'session' => 'required',
    //                     'challan_date' => 'required',
    //                 ]
    //             );

    //             if ($validator->fails()) {
    //                 // Get the first error message
    //                 $messages = $validator->getMessageBag();

    //                 return response()->json([
    //                     'success' => false,
    //                     'error' => $messages->first(),
    //                 ], 422);
    //             }
    //             $students = StudentRegistration::where('student_status', 'Enrolled')->where('roll_no', $request->input('student'))->get();
    //             $query = StudentRegistration::where('student_status', 'Enrolled')->where('roll_no', $request->input('student'));
    //             $existingChallanStudent = Challans::where('student_id', $students[0]->id)->whereYear('fee_month', $year)->whereMonth('fee_month', $month)->where('challan_type', 'Regular')->exists();
    //             if ($existingChallanStudent) {
    //                 return response()->json(['error' => true, 'message' => 'Challan for this student has already been generated for this month.']);
    //             }
    //         }
    //         if (! empty($request->class) && $request->class != 'all') {
    //             $query = $query->where('class_id', $request->input('class'));
    //         }
    //         $students = $query->get();
    //         // if ($request->input('student') === 'all') {
    //         //     $existingChallanClass = Challans::where('class_id', $request->input('class'))
    //         //         ->whereYear('challan_date', $year)
    //         //         ->whereMonth('challan_date', $month)
    //         //         ->exists();
    //         //     if ($existingChallanClass) {
    //         //         return response()->json(['error' => true, 'message' => 'Challan for this class has already been generated for this month.']);
    //         //     }
    //         // } else {
    //         //     $student = StudentRegistration::where('id', $request->input('student'))->first();
    //         //     $students[] = $student;
    //         //     $existingChallanStudent = Challans::where('student_id', $student->id)
    //         //         ->whereYear('challan_date', $year)
    //         //         ->whereMonth('challan_date', $month)
    //         //         ->exists();
    //         //     if ($existingChallanStudent) {
    //         //         return response()->json(['error' => true, 'message' => 'Challan for this student has already been generated for this month.']);
    //         //     }
    //         // }
    //         foreach ($students as $student) {
    //             $total = 0;
    //             $concession_amount = 0;
    //             $item = [];
    //             $itemIndex = 0;

    //             $existingChallanStudent = Challans::where('student_id', $student->id)->whereYear('fee_month', $year)->whereMonth('fee_month', $month)->where('challan_type', 'Regular')->exists();
    //             if ($existingChallanStudent) {
    //                 continue;
    //             } else {
    //                 if ($student->enrollment->adm_date > $fee_month) {
    //                     continue;
    //                 }
    //                 $fee_heads = StudentFeeStructure::with('feehead')->where('reg_id', $student->id)->where('checked_status', 1)->where('owned_by', $student->owned_by)->get();
    //                 $concession = Concession::with('concession')->where('student_id', $student->id)
    //                     ->where('end_date', '>=', date('Y-m-d'))
    //                     ->orderBy('id', 'desc')
    //                     ->where('status', 'Approved')
    //                     ->first();
    //                 if (! $concession) {
    //                     $concession = Concession::with('concession')->where('student_id', $student->id)
    //                         ->orderBy('id', 'desc')
    //                         ->whereNull('end_date')
    //                         ->where('status', 'Approved')
    //                         ->first();
    //                 }

    //                 $challan = new Challans;
    //                 $challan->student_id = $student->id;
    //                 $challan->rollno = $student->roll_no;
    //                 $challan->class_id = $student->class_id;
    //                 $challan->challanNo = $this->challanNo();
    //                 $challan->challan_date = date('Y-m-d');
    //                 $challan->fee_month = date('Y-m-01', strtotime($fee_month));
    //                 $challan->challan_type = 'Regular';
    //                 $challan->total_amount = $total;
    //                 $challan->issue_date = $issueDate;
    //                 $challan->due_date = $dueDate;
    //                 $challan->status = 'Issued';
    //                 $challan->owned_by = $student->owned_by;
    //                 $challan->created_by = $student->created_by;
    //                 $challan->session_id = $request->session;
    //                 $challan->concession_id = $concession ? $concession->concession_id : '';
    //                 $challan->concession_amount = $concession_amount;
    //                 $challan->save();

    //                 foreach ($fee_heads as $fee_head) {
    //                     if (
    //                         strpos(strtolower($fee_head->feehead->fee_head), 'security') != false || strpos(strtolower($fee_head->feehead->fee_head), 're-admission') != false ||
    //                         strpos(strtolower($fee_head->feehead->fee_head), 'transfer') != false || strpos(strtolower($fee_head->feehead->fee_head), 'admission') != false || strpos(strtolower($fee_head->feehead->fee_head), 'registration') != false
    //                     ) {
    //                         continue;
    //                     }

    //                     if ($concession) {
    //                         $conession_head = ConcessionPolicyHead::where('head_id', $fee_head->head_id)->where('concession_id', $concession->concession_id)->first();
    //                         if ($conession_head) {
    //                             $concessionAmount = round(($fee_head->amount / 100) * $conession_head->percentage);
    //                         } else {
    //                             $concessionAmount = round(($fee_head->amount / 100) * $fee_head->discount);
    //                         }
    //                     } else {
    //                         $concessionAmount = round(($fee_head->amount / 100) * $fee_head->discount);
    //                     }
    //                     $total += $fee_head->amount;
    //                     $concession_amount += @$concessionAmount ? $concessionAmount : 0;

    //                     $challan_head = new ChallanHead;
    //                     $challan_head->challan_id = $challan->id ?? 0;
    //                     $challan_head->head_id = $fee_head->head_id;
    //                     $challan_head->price = $fee_head->amount ? $fee_head->amount : 0;
    //                     $challan_head->concession = @$concessionAmount ? @$concessionAmount : 0;
    //                     $challan_head->save();

    //                     $item[$itemIndex]['prod_id'] = $challan_head->id;
    //                     $item[$itemIndex]['head'] = $fee_head->head_id;
    //                     $item[$itemIndex]['price'] = $fee_head->amount ? $fee_head->amount : 0;
    //                     $item[$itemIndex]['quantity'] = 1;
    //                     $item[$itemIndex]['concession'] = @$concessionAmount ? @$concessionAmount : 0;
    //                     $item[$itemIndex]['total'] = $total;
    //                     $itemIndex++;
    //                 }

    //                 // $challan->concession_id = $concession ? $concession->concession_id : '';
    //                 $challan->concession_amount = $concession_amount;
    //                 $challan->total_amount = $total;
    //                 $challan->save();
    //                 // dd($concession_amount,$total);

    //                 $data['id'] = $challan->id;
    //                 $data['no'] = $challan->challanNo;
    //                 $data['date'] = $challan->challan_date;
    //                 $data['reference'] = $challan->student_id;
    //                 $data['category'] = 'Regular';
    //                 $data['user_id'] = $student->id;
    //                 $data['user_type'] = 'Student';
    //                 $data['owned_by'] = $challan->owned_by;
    //                 $data['created_by'] = $challan->created_by;
    //                 $data['items'] = $item;

    //                 $dataret = Utility::jrentry($data);
    //                 $challan->voucher_id = $dataret;
    //                 $challan->save();
    //                 $gen_count++;
    //             }
    //         }
    //         DB::commit();

    //         return response()->json(['success' => true, 'message' => $gen_count.' Bulk Challans generated successfully.']);
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         dd($e);

    //         return response()->json(['error' => true, 'message' => 'Error']);
    //     }
    // }
private function appliesJunJulFeeExemption(?StudentRegistration $student, Carbon $feeMonth): bool
    {
        if (!$student || empty($student->fee_exempt_jun_jul) || !$student->enrollment || empty($student->enrollment->adm_date)) {
            return false;
        }

        if (!in_array((int) $feeMonth->month, [6, 7], true)) {
            return false;
        }

        return (int) Carbon::parse($student->enrollment->adm_date)->year === (int) $feeMonth->year;
    }
private function challanHasJunJulExemptionLabel(?Challans $challan): bool
    {
        if (!$challan) {
            return false;
        }

        $student = $challan->student;
        if (!$student instanceof StudentRegistration) {
            $student = StudentRegistration::with('enrollment')->find($challan->student_id);
        } elseif (!$student->relationLoaded('enrollment')) {
            $student->load('enrollment');
        }

        if (!$student || empty($student->fee_exempt_jun_jul) || !$student->enrollment || empty($student->enrollment->adm_date)) {
            return false;
        }

        $months = collect();
        if (!empty($challan->other_months)) {
            $months = collect(explode(',', $challan->other_months))
                ->map(fn($month) => trim($month))
                ->filter();
        }

        if ($months->isEmpty() && !empty($challan->fee_month)) {
            $months = collect([$challan->fee_month]);
        }

        if ($months->isEmpty()) {
            return false;
        }

        $admissionYear = (int) Carbon::parse($student->enrollment->adm_date)->year;

        return $months->every(function ($month) use ($admissionYear) {
            $billingMonth = Carbon::parse($month);

            return in_array((int) $billingMonth->month, [6, 7], true)
                && (int) $billingMonth->year === $admissionYear;
        });
    }
    public function bulkchallan(Request $request)
    {
        set_time_limit(0);

        $gen_count = 0;
        $update_count = 0;
        $today = \Carbon\Carbon::now()->startOfDay();

        $fee_month = Carbon::parse($request->challan_date);
        $year = $fee_month->year;
        $month = $fee_month->month;
        $targetDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $feeMonthFormatted = $fee_month->format('Y-m');

        // ----------------------------------------------------------------
        // 1. Resolve issue date and due date
        // ----------------------------------------------------------------
        if ($request->filled('issue_date')) {
            $issueDate = Carbon::parse($request->issue_date)->toDateString();
        } else {
            $issueDate = Carbon::parse($request->challan_date)
                ->subMonthNoOverflow()->day(26)->toDateString();
        }

        if ($request->filled('due_date')) {
            $dueDate = Carbon::parse($request->due_date)->toDateString();
        } else {
            $dueFallback = Carbon::parse($request->challan_date)->day(8);
            if ($dueFallback->isSaturday())
                $dueFallback->addDays(2);
            elseif ($dueFallback->isSunday())
                $dueFallback->addDay();
            $dueDate = $dueFallback->toDateString();
        }

        // ----------------------------------------------------------------
        // 2. Subscription duration (used only for NEW challans — PATH B)
        // ----------------------------------------------------------------
        $subscriptionMap = [
            'monthly' => 1,
            'bi-monthly' => 2,
            'quarterly' => 3,
            '4-monthly' => 4,
            '5-monthly' => 5,
            '6-monthly' => 6,
            '7-monthly' => 7,
            '8-monthly' => 8,
            '9-monthly' => 9,
            '10-monthly' => 10,
            '11-monthly' => 11,
            'yearly' => 12,
        ];
        $feeSubscription = $request->input('fee_subscription', 'monthly');
        $duration = $subscriptionMap[$feeSubscription] ?? 1;
        $subscriptionMontsdate = [];
        $subscriptionMonthDates = [];
        for ($i = 0; $i < $duration; $i++) {
            $monthToAdd = $i;
            $subscriptionMonthDates[] = Carbon::parse($targetDate)->addMonthsNoOverflow($monthToAdd)->format('Y-m-d');
            $subscriptionMontsdate[] = Carbon::parse($targetDate)->addMonthsNoOverflow($monthToAdd)->format('Y-m');
        }
        // ----------------------------------------------------------------
        // 3. Load shared fee heads ONCE
        // ----------------------------------------------------------------
        $tuitionFeeHead = FeeHead::where('fee_head', 'LIKE', '%Tuition%')->first();
        $lateFeeHead = FeeHead::where('fee_head', 'LIKE', '%LATE FEE%')->first();

        // ----------------------------------------------------------------
        // 4. Validate + Pre-Challan Report check + Build student query
        // ----------------------------------------------------------------
        $isSingleStudent = ($request->input('student') !== 'all');

        if (!$isSingleStudent) {

            $validator = \Validator::make($request->all(), [
                'branches' => 'required',
                'session' => 'required',
                'class' => 'required',
                'challan_date' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->getMessageBag()->first(),
                ], 422);
            }

            // Pre-challan report check
            if ($request->branches == 'all') {
                $allBranchIds = \App\Models\Branch::pluck('id');
                $approvedBranchIds = \App\Models\PreChallanReport::whereIn('month', $subscriptionMontsdate ?: [$feeMonthFormatted])
                    ->where('status', 'Approved')->pluck('branch_id');
                $missingBranches = $allBranchIds->diff($approvedBranchIds);

                if ($missingBranches->isNotEmpty()) {
                    $missingNames = \App\Models\Branch::whereIn('id', $missingBranches)
                        ->pluck('name')->implode(', ');
                    return response()->json([
                        'error' => true,
                        'message' => 'Pre-challan report for ' . $feeMonthFormatted
                            . ' is not approved for: ' . $missingNames
                            . '. Please get them approved first.',
                    ]);
                }
            } else {
                $preReport = \App\Models\PreChallanReport::where('branch_id', $request->branches)
                    ->whereIn('month', $subscriptionMontsdate ?: [$feeMonthFormatted])
                    ->where('status', 'Approved')
                    ->exists();
                if (!$preReport) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Pre-challan report for ' . $feeMonthFormatted
                            . ' is not approved for this branch.',
                    ]);
                }
            }

            $query = ($request->branches == 'all')
                ? StudentRegistration::where('student_status', 'Enrolled')
                : StudentRegistration::where('owned_by', $request->branches)
                    ->where('student_status', 'Enrolled');

        } else {

            $validator = \Validator::make($request->all(), [
                'session' => 'required',
                'challan_date' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->getMessageBag()->first(),
                ], 422);
            }

            $query = StudentRegistration::where('student_status', 'Enrolled')
                ->where('roll_no', $request->input('student'));
            $singleStudent = $query->with('enrollment')->first();

            if (!$singleStudent) {
                return response()->json(['error' => true, 'message' => 'Student not found.']);
            }

            $preReport = \App\Models\PreChallanReport::where('branch_id', $singleStudent->owned_by)
                ->whereIn('month', $subscriptionMontsdate ?: [$feeMonthFormatted])
                ->where('status', 'Approved')
                ->exists();
            if (!$preReport) {
                return response()->json([
                    'error' => true,
                    'message' => 'Pre-challan report for ' . $feeMonthFormatted
                        . ' is not approved for this student\'s branch.',
                ]);
            }

            if ($duration === 1 && $tuitionFeeHead) {
                $admissionWithTuition = Challans::where('student_id', $singleStudent->id)
                    ->where('challan_type', 'Admission')
                    ->where(function ($q) use ($targetDate) {
                        $q->whereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') = ?", [$targetDate])
                            ->orWhereRaw("FIND_IN_SET(?, other_months)", [$targetDate]);
                    })
                    ->whereHas('heads', fn($q) => $q->where('head_id', $tuitionFeeHead->id))
                    ->exists();

                if ($admissionWithTuition && !$this->appliesJunJulFeeExemption($singleStudent, $fee_month)) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Challan already generated for this month.',
                    ]);
                }
            }
        }

        if (!empty($request->class) && $request->class != 'all') {
            $query->where('class_id', $request->input('class'));
        }

        // ----------------------------------------------------------------
        // 5. Load ALL students with enrollment eager-loaded
        // ----------------------------------------------------------------
        $students = $query->with('enrollment')->get();

        if ($students->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => '0 Challan(s) generated and 0 Challan(s) updated.',
                'generated' => 0,
                'updated' => 0,
            ]);
        }

        $studentIds = $students->pluck('id')->toArray();

        // ----------------------------------------------------------------
        // 6. Build student → branch map (used for fee structure filtering)
        // ----------------------------------------------------------------
        // Maps student.id => student.owned_by (branch_id) so we can filter
        // StudentFeeStructure rows to only those belonging to the student's branch.
        $studentBranchMap = $students->pluck('owned_by', 'id'); // [student_id => branch_id]

        // ----------------------------------------------------------------
        // 7. BULK PRE-LOAD all data — zero N+1 queries
        // ----------------------------------------------------------------

        // 7a. Checked fee structure heads grouped by student
        //     Filter in-memory by owned_by so each student only gets their
        //     branch's fee structure (handles both single-branch and "all" cases).
        $allCheckedFeeHeads = StudentFeeStructure::with('feehead')
            ->whereIn('reg_id', $studentIds)
            ->where('checked_status', 1)
            ->get()
            ->filter(fn($row) => $studentBranchMap->get($row->reg_id) == $row->owned_by)
            ->groupBy('reg_id');

        // 7b. All fee structure heads (checked + unchecked) grouped by student
        //     Same owned_by branch filter applied here as well.
        $allFeeHeadsGrouped = StudentFeeStructure::whereIn('reg_id', $studentIds)
            ->get()
            ->filter(fn($row) => $studentBranchMap->get($row->reg_id) == $row->owned_by)
            ->groupBy('reg_id')
            ->map(fn($group) => $group->keyBy('head_id'));

        // 7c. Active concessions per student
        $concessions = Concession::with('concession')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'Approved')
            ->where(function ($q) {
                $q->where('end_date', '>=', date('Y-m-d'))->orWhereNull('end_date');
            })
            ->where('active_status',1)
            ->orderBy('end_date', 'desc')
            ->get()
            ->groupBy('student_id')
            ->map(fn($group) => $group->first(fn($c) => $c->end_date !== null) ?? $group->first());

        // 7d. Existing Regular/Advance challans for this month per student
        $existingChallans = Challans::whereIn('student_id', $studentIds)
            ->whereIn('challan_type', ['Regular', 'Advance'])
            ->where(function ($q) use ($subscriptionMonthDates) {
                foreach ($subscriptionMonthDates as $monthDate) {
                    $q->orWhereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') = ?", [$monthDate])
                        ->orWhereRaw("FIND_IN_SET(?, REPLACE(other_months, ' ', ''))", [$monthDate]);
                }
            })
            ->get()
            ->groupBy('student_id');

        // 7e. Admission challans WITH tuition head for this month — O(1) lookup set
        $admissionWithTuitionChallans = collect();
        if ($tuitionFeeHead) {
            $admissionWithTuitionChallans = Challans::whereIn('student_id', $studentIds)
                ->where('challan_type', 'Admission')
                ->where(function ($q) use ($subscriptionMonthDates) {
                    foreach ($subscriptionMonthDates as $monthDate) {
                        $q->orWhereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') = ?", [$monthDate])
                            ->orWhereRaw("FIND_IN_SET(?, REPLACE(other_months, ' ', ''))", [$monthDate]);
                    }
                })
                ->whereHas('heads', fn($q) => $q->where('head_id', $tuitionFeeHead->id))
                ->get()
                ->groupBy('student_id');
        }

        // 7f. Concession policy heads keyed by concession_id → head_id
        $concessionIds = $concessions->pluck('concession_id')->unique()->filter()->toArray();
        $concessionPolicyHeads = collect();
        if (!empty($concessionIds)) {
            $concessionPolicyHeads = ConcessionPolicyHead::whereIn('concession_id', $concessionIds)
                ->get()
                ->groupBy('concession_id')
                ->map(fn($group) => $group->keyBy('head_id'));
        }
        // dd($concessionIds);
        // 7g. Existing challan heads for existing challans
        $existingChallanIds = $existingChallans->flatten()->pluck('id')->toArray();
        $existingChallanHeads = collect();
        if (!empty($existingChallanIds)) {
            $existingChallanHeads = ChallanHead::whereIn('challan_id', $existingChallanIds)
                ->get()
                ->groupBy('challan_id');
        }

        // 7h. All issued challans per student + already-late-fee'd challan ids
        $allIssuedChallans = Challans::whereIn('student_id', $studentIds)
            ->whereRaw('LOWER(status) = ?', ['issued'])
            ->whereNotIn('challan_type', ['Registration', 'Withdrawal', 'Transfer'])
            ->get()
            ->groupBy('student_id');

        $issuedChallanIds = $allIssuedChallans->flatten()->pluck('id')->toArray();
        $lateFeeChallanIds = collect();
        if ($lateFeeHead && !empty($issuedChallanIds)) {
            $lateFeeChallanIds = ChallanHead::whereIn('challan_id', $issuedChallanIds)
                ->where('head_id', $lateFeeHead->id)
                ->pluck('challan_id')
                ->flip();
        }

        // ----------------------------------------------------------------
        // 8. Shared helper: head-name skip filter
        // ----------------------------------------------------------------
        $isSkippableHead = fn($name) => str_contains($name, 'security')
            || str_contains($name, 're-admission')
            || str_contains($name, 'transfer')
            || str_contains($name, 'admission')
            || str_contains($name, 'registration');

        // ----------------------------------------------------------------
        // 9. Shared factory: build a calcAmounts closure for a given duration
        //    Kept as a factory so PATH A and PATH B each get their own
        //    closure with the correct multiplier baked in.
        // ----------------------------------------------------------------
        $makeCalcAmounts = function (int $effectiveDuration) use ($concessionPolicyHeads) {
            return function ($fee_head, $concession) use ($effectiveDuration, $concessionPolicyHeads) {
                $headNameLower = strtolower($fee_head->feehead->fee_head ?? '');
                $isOneTime = str_contains($headNameLower, 'annual fee')
                    || str_contains($headNameLower, 'late fee');
                $multiplier = $isOneTime ? ($effectiveDuration > 0 ? 1 : 0) : $effectiveDuration;
                $baseAmount = $fee_head->amount * $multiplier;

                if ($concession) {
                    $policyHead = $concessionPolicyHeads->get($concession->concession_id)
                            ?->get($fee_head->head_id);
                    $concessionAmount = $policyHead
                        ? round(($baseAmount / 100) * $policyHead->percentage)
                        : round(($baseAmount / 100) * $fee_head->discount);
                } else {
                    $concessionAmount = round(($baseAmount / 100) * $fee_head->discount);
                }

                return [$baseAmount, $concessionAmount];
            };
        };

        $challanMonths = function ($challan) {
            $months = [];

            if (!empty($challan->fee_month)) {
                $months[] = Carbon::parse($challan->fee_month)->format('Y-m-d');
            }

            if (!empty($challan->other_months)) {
                foreach (explode(',', $challan->other_months) as $monthValue) {
                    $monthValue = trim($monthValue);
                    if ($monthValue !== '') {
                        $months[] = Carbon::parse($monthValue)->format('Y-m-d');
                    }
                }
            }

            return collect($months)->unique()->sort()->values();
        };

        // ----------------------------------------------------------------
        // 10. Process each student — per-student transaction
        // ----------------------------------------------------------------
        foreach ($students as $student) {

            // Skip if no enrollment or admitted after fee month
            if (!$student->enrollment || $student->enrollment->adm_date > $fee_month) {
                continue;
            }
            $firstAdmissionChallan = Challans::where('student_id', $student->id)
                ->where('challan_type', 'Admission')
                ->orderBy('fee_month', 'asc')
                ->first();

            if ($firstAdmissionChallan) {
                $admissionFeeMonth = Carbon::parse($firstAdmissionChallan->fee_month)->startOfMonth();
                $currentTargetMonth = Carbon::parse($targetDate)->startOfMonth();

                // If the admission's first billing month is still in the future
                // relative to what we're generating now, skip entirely.
                if ($admissionFeeMonth->gt($currentTargetMonth)) {
                    continue;
                }
            }
            $studentExistingChallans = $existingChallans->get($student->id, collect());
            $studentAdmissionChallans = $admissionWithTuitionChallans->get($student->id, collect());
            $coverageChallans = $studentExistingChallans->merge($studentAdmissionChallans);

            $coveredMonthDates = $coverageChallans
                ->flatMap(fn($challan) => $challanMonths($challan))
                ->intersect($subscriptionMonthDates)
                ->unique()
                ->values();

            $remainingMonthDates = collect($subscriptionMonthDates)
                ->diff($coveredMonthDates)
                ->values();

            $existingChallan = $studentExistingChallans
                ->filter(fn($challan) => strtolower(trim($challan->status ?? '')) !== 'paid')
                ->sortByDesc(fn($challan) => strtolower(trim($challan->challan_type ?? '')) === 'advance' ? 1 : 0)
                ->first(function ($challan) use ($challanMonths, $subscriptionMonthDates) {
                    return $challanMonths($challan)->intersect($subscriptionMonthDates)->isNotEmpty();
                });

            if (!$existingChallan && $remainingMonthDates->isEmpty()) {
                continue;
            }

            $fee_heads = $allCheckedFeeHeads->get($student->id, collect());
            $allFeeHeadsKeyed = $allFeeHeadsGrouped->get($student->id, collect());
            $concession = $concessions->get($student->id);

            \DB::beginTransaction();
            try {

                // ============================================================
                // PATH A — UPDATE existing challan
                // ============================================================
                if ($existingChallan) {

                    $total = 0;
                    $concession_amount = 0;
                    $item = [];
                    $itemIndex = 0;

                    // ── Derive effective duration from existing challan ───────
                    // The challan may have been originally generated for multiple
                    // months (e.g. 4-monthly). other_months stores the full list
                    // of covered dates as comma-separated values.
                    // We count them to determine the correct multiplier so that
                    // head amounts are NOT collapsed to single-month values.
                    $updatedMonthDates = $challanMonths($existingChallan)
                        ->merge($remainingMonthDates)
                        ->unique()
                        ->sort()
                        ->values();

                    $chargeableMonthCount = $updatedMonthDates
                        ->filter(fn($monthDate) => !$this->appliesJunJulFeeExemption($student, Carbon::parse($monthDate)))
                        ->count();

                    $existingChallan->other_months = $updatedMonthDates->implode(',');
                    $existingChallan->fee_month = $updatedMonthDates->first() ?: $existingChallan->fee_month;

                    // Build calcAmounts with the effective duration for this challan
                    $calcAmounts = $makeCalcAmounts($chargeableMonthCount);
                    $attachedHeads = $existingChallanHeads->get($existingChallan->id, collect());
                    $attachedMap = $attachedHeads->keyBy('head_id');

                    // ── Update / insert checked heads ─────────────────────────
                    foreach ($fee_heads as $fee_head) {
                        if ($isSkippableHead(strtolower($fee_head->feehead->fee_head ?? ''))) {
                            continue;
                        }

                        [$baseAmount, $concessionAmount] = $calcAmounts($fee_head, $concession);

                        if ($attachedMap->has($fee_head->head_id)) {
                            $challanHead = $attachedMap->get($fee_head->head_id);
                            $challanHead->price = $baseAmount;
                            $challanHead->concession = $concessionAmount;
                            $challanHead->save();
                        } else {
                            $challanHead = ChallanHead::create([
                                'challan_id' => $existingChallan->id,
                                'head_id' => $fee_head->head_id,
                                'price' => $baseAmount,
                                'concession' => $concessionAmount,
                                'paid' => 0,
                            ]);
                        }

                        $total += $baseAmount;
                        $concession_amount += $concessionAmount;
                        $item[$itemIndex++] = [
                            'prod_id' => $challanHead->id,
                            'head' => $fee_head->head_id,
                            'price' => $baseAmount,
                            'quantity' => 1,
                            'concession' => $concessionAmount,
                            'total' => $total,
                        ];
                    }

                    // ── Remove unchecked + unpaid; keep paid heads ────────────
                    foreach ($attachedHeads as $attachedHead) {
                        $structureRow = $allFeeHeadsKeyed->get($attachedHead->head_id);
                        if ($structureRow && $structureRow->checked_status == 1) {
                            continue; // already handled above
                        }

                        if ($attachedHead->paid > 0) {
                            // Paid — keep it, include in totals
                            $total += $attachedHead->price;
                            $concession_amount += $attachedHead->concession;
                            $item[$itemIndex++] = [
                                'prod_id' => $attachedHead->id,
                                'head' => $attachedHead->head_id,
                                'price' => $attachedHead->price,
                                'quantity' => 1,
                                'concession' => $attachedHead->concession,
                                'total' => $total,
                            ];
                        } else {
                            // Unpaid + unchecked — remove head and its journal lines
                            JournalItem::where('journal', $existingChallan->voucher_id)
                                ->where('entry_id', $attachedHead->id)
                                ->delete();
                            $attachedHead->delete();
                        }
                    }

                    // ── Persist updated challan totals ────────────────────────
                    $existingChallan->concession_amount = $concession_amount;
                    $existingChallan->total_amount = $total;
                    $existingChallan->concession_id = $concession ? $concession->concession_id : '';
                    $existingChallan->save();

                    // ── Rebuild voucher (wipe old journal items, re-create) ───
                    JournalItem::where('journal', $existingChallan->voucher_id)->delete();

                    $voucherId = Utility::jrentry([
                        'id' => $existingChallan->id,
                        'no' => $existingChallan->challanNo,
                        'date' => $existingChallan->challan_date,
                        'reference' => $existingChallan->student_id,
                        'category' => $existingChallan->challan_type,
                        'user_id' => $student->id,
                        'user_type' => 'Student',
                        'owned_by' => $existingChallan->owned_by,
                        'created_by' => $existingChallan->created_by,
                        'items' => $item,
                    ]);

                    if ($voucherId && $voucherId != $existingChallan->voucher_id) {
                        $existingChallan->voucher_id = $voucherId;
                        $existingChallan->save();
                    }

                    $currentChallanId = $existingChallan->id;
                    $update_count++;

                } else {
                    // ============================================================
                    // PATH B — CREATE new challan
                    // Uses $duration from the request (correct for new generation)
                    // ============================================================
                    $total = 0;
                    $concession_amount = 0;
                    $item = [];
                    $itemIndex = 0;

                    $generatedMonthDates = $remainingMonthDates->values();
                    $chargeableMonthCount = $generatedMonthDates
                        ->filter(fn($monthDate) => !$this->appliesJunJulFeeExemption($student, Carbon::parse($monthDate)))
                        ->count();

                    // Build calcAmounts with only the months still missing for this student.
                    $calcAmounts = $makeCalcAmounts($chargeableMonthCount);

                    $challan = new Challans;
                    $challan->student_id = $student->id;
                    $challan->rollno = $student->roll_no;
                    $challan->class_id = $student->class_id;
                    $challan->challanNo = $this->challanNo();
                    $challan->challan_date = date('Y-m-d');
                    $challan->fee_month = $generatedMonthDates->first() ?: date('Y-m-01', strtotime($fee_month));
                    $challan->challan_type = 'Regular';
                    $challan->total_amount = 0;
                    $challan->issue_date = $issueDate;
                    $challan->due_date = $dueDate;
                    $challan->status = 'Issued';
                    $challan->owned_by = $student->owned_by;
                    $challan->created_by = $student->created_by;
                    $challan->session_id = $request->session;
                    $challan->concession_id = $concession ? $concession->concession_id : '';
                    $challan->concession_amount = 0;
                    $challan->other_months = $duration > 1
                        ? $generatedMonthDates->implode(',')
                        : null;

                    $challan->save();

                    foreach ($fee_heads as $fee_head) {
                        if ($isSkippableHead(strtolower($fee_head->feehead->fee_head ?? ''))) {
                            continue;
                        }

                        [$baseAmount, $concessionAmount] = $calcAmounts($fee_head, $concession);

                        $total += $baseAmount;
                        $concession_amount += $concessionAmount;

                        $challan_head = new ChallanHead;
                        $challan_head->challan_id = $challan->id;
                        $challan_head->head_id = $fee_head->head_id;
                        $challan_head->price = $baseAmount;
                        $challan_head->concession = $concessionAmount;
                        $challan_head->save();

                        $item[$itemIndex++] = [
                            'prod_id' => $challan_head->id,
                            'head' => $fee_head->head_id,
                            'price' => $baseAmount,
                            'quantity' => 1,
                            'concession' => $concessionAmount,
                            'total' => $total,
                        ];
                    }

                    $challan->concession_amount = $concession_amount;
                    $challan->total_amount = $total;
                    $challan->save();

                    $dataret = Utility::jrentry([
                        'id' => $challan->id,
                        'no' => $challan->challanNo,
                        'date' => $challan->challan_date,
                        'reference' => $challan->student_id,
                        'category' => 'Regular',
                        'user_id' => $student->id,
                        'user_type' => 'Student',
                        'owned_by' => $challan->owned_by,
                        'created_by' => $challan->created_by,
                        'items' => $item,
                    ]);
                    $challan->voucher_id = $dataret;
                    $challan->save();

                    $currentChallanId = $challan->id;
                    $gen_count++;
                }

                // ================================================================
                // 11. Late fee — pre-loaded collections, minimal extra queries
                // ================================================================
                if ($lateFeeHead) {

                    $issuedForStudent = $allIssuedChallans->get($student->id, collect());

                    foreach ($issuedForStudent as $oldChallan) {

                        if ($oldChallan->id === $currentChallanId)
                            continue;
                        if ($lateFeeChallanIds->has($oldChallan->id))
                            continue;

                        $oldDueDate = Carbon::parse($oldChallan->due_date);
                        if ($oldDueDate->isSaturday())
                            $oldDueDate->addDays(2);
                        elseif ($oldDueDate->isSunday())
                            $oldDueDate->addDay();

                        if ($today->lte($oldDueDate))
                            continue;

                        $daysOverdue = min($today->diffInDays($oldDueDate), 10);
                        $lateFeeAmount = $daysOverdue * 120;
                        if ($lateFeeAmount <= 0)
                            continue;

                        $lateFeeHeadRecord = ChallanHead::create([
                            'challan_id' => $oldChallan->id,
                            'head_id' => $lateFeeHead->id,
                            'price' => $lateFeeAmount,
                            'concession' => 0,
                            'paid' => 0,
                        ]);

                        JournalItem::insert([
                            [
                                'journal' => $oldChallan->voucher_id,
                                'account' => $lateFeeHead->account_id,
                                'head' => $lateFeeHead->id,
                                'entry_id' => $lateFeeHeadRecord->id,
                                'user_id' => $oldChallan->student_id,
                                'user_type' => 'Student',
                                'types' => 'Challan',
                                'credit' => $lateFeeAmount,
                                'debit' => 0,
                                'description' => 'Late Fee Income - Challan No ' . $oldChallan->challanNo,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ],
                            [
                                'journal' => $oldChallan->voucher_id,
                                'account' => $lateFeeHead->receivable_account_id,
                                'head' => $lateFeeHead->id,
                                'entry_id' => $lateFeeHeadRecord->id,
                                'user_id' => $oldChallan->student_id,
                                'user_type' => 'Student',
                                'types' => 'Challan',
                                'credit' => 0,
                                'debit' => $lateFeeAmount,
                                'description' => 'Late Fee Receivable - Challan No ' . $oldChallan->challanNo,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ],
                        ]);

                        \DB::table('challans')->where('id', $oldChallan->id)
                            ->increment('total_amount', $lateFeeAmount);

                        $lateFeeChallanIds->put($oldChallan->id, true);
                    }
                }

                \DB::commit();

            } catch (\Exception $e) {
                \DB::rollback();
                \Log::error('Bulk challan failed for student ' . $student->id, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'student' => $student->id,
                ]);
                continue;
            }

            unset($challan, $existingChallan, $currentChallanId);
        }

        return response()->json([
            'success' => true,
            'message' => $gen_count . ' Challan(s) generated and ' . $update_count . ' Challan(s) updated successfully.',
            'generated' => $gen_count,
            'updated' => $update_count,
        ]);
    }
    //previous corrected one
    // public function bulkchallan(Request $request)
    // {
    //     \DB::beginTransaction();
    //     try {
    //         $students = [];
    //         $gen_count = 0;
    //         $today = \Carbon\Carbon::now();

    //         $fee_month = Carbon::parse($request->challan_date);
    //         $year = $fee_month->year;
    //         $month = $fee_month->month;

    //         // ----------------------------------------------------------------
    //         // 1. Resolve issue date and due date
    //         //    Priority: explicit form fields (issue_date / due_date) that the
    //         //    blade maps from from_date / to_date for the bulk-generate path.
    //         //    Fallback: original server-side computation.
    //         // ----------------------------------------------------------------
    //         if ($request->filled('issue_date')) {
    //             $issueDate = Carbon::parse($request->issue_date)->toDateString();
    //         } else {
    //             $issueDate = Carbon::parse($request->challan_date)
    //                 ->subMonthNoOverflow()
    //                 ->day(26)
    //                 ->toDateString();
    //         }

    //         if ($request->filled('due_date')) {
    //             $dueDate = Carbon::parse($request->due_date)->toDateString();
    //         } else {
    //             $dueFallback = Carbon::parse($request->challan_date)->day(8);
    //             if ($dueFallback->isSunday()) {
    //                 $dueFallback->addDay();
    //             }
    //             $dueDate = $dueFallback->toDateString();
    //         }

    //         // ----------------------------------------------------------------
    //         // 2. Resolve fee subscription duration
    //         // ----------------------------------------------------------------
    //         $subscriptionMap = [
    //             'monthly' => 1,
    //             'bi-monthly' => 2,
    //             'quarterly' => 3,
    //             '4-monthly' => 4,
    //             '5-monthly' => 5,
    //             '6-monthly' => 6,
    //             '7-monthly' => 7,
    //             '8-monthly' => 8,
    //             '9-monthly' => 9,
    //             '10-monthly' => 10,
    //             '11-monthly' => 11,
    //             'yearly' => 12,
    //         ];

    //         $feeSubscription = $request->input('fee_subscription', 'monthly');
    //         $duration = $subscriptionMap[$feeSubscription] ?? 1;

    //         // ----------------------------------------------------------------
    //         // 3. Build the student query
    //         // ----------------------------------------------------------------
    //         if ($request->input('student') == 'all') {
    //             $validator = \Validator::make(
    //                 $request->all(),
    //                 [
    //                     'branches' => 'required',
    //                     'session' => 'required',
    //                     'class' => 'required',
    //                     'challan_date' => 'required',
    //                 ]
    //             );

    //             if ($validator->fails()) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'error' => $validator->getMessageBag()->first(),
    //                 ], 422);
    //             }

    //             $query = StudentRegistration::where('owned_by', $request->branches)
    //                 ->where('student_status', 'Enrolled');
    //         } else {
    //             $validator = \Validator::make(
    //                 $request->all(),
    //                 [
    //                     'session' => 'required',
    //                     'challan_date' => 'required',
    //                 ]
    //             );

    //             if ($validator->fails()) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'error' => $validator->getMessageBag()->first(),
    //                 ], 422);
    //             }

    //             $students = StudentRegistration::where('student_status', 'Enrolled')
    //                 ->where('roll_no', $request->input('student'))
    //                 ->get();

    //             $query = StudentRegistration::where('student_status', 'Enrolled')
    //                 ->where('roll_no', $request->input('student'));

    //             // Guard: single-student duplicate check
    //             $targetDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';

    //             $existingChallanStudent = Challans::where('student_id', $students[0]->id)
    //                 ->whereIn('challan_type', ['Regular', 'Advance'])
    //                 ->where(function ($query) use ($targetDate) {
    //                     $query->whereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') = ?", [$targetDate])
    //                         ->orWhereRaw("FIND_IN_SET(?, other_months)", [$targetDate]);
    //                 })
    //                 ->exists();
    //             $tuiton_fee = FeeHead::where('fee_head', 'LIKE', '%Tuition%')->first();

    //             $admissionWithTuition = false;

    //             if ($tuiton_fee) {
    //                 $admissionWithTuition = Challans::where('student_id', $students[0]->id)
    //                     ->where('challan_type', 'Admission')
    //                     ->where(function ($query) use ($targetDate) {
    //                         $query->whereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') = ?", [$targetDate])
    //                             ->orWhereRaw("FIND_IN_SET(?, other_months)", [$targetDate]);
    //                     })
    //                     ->whereHas('heads', function ($q) use ($tuiton_fee) {
    //                         $q->where('head_id', $tuiton_fee->id); // ✅ FIXED COLUMN
    //                     })
    //                     ->exists();
    //             }
    //             // 🔴 Check Admission FIRST installment (has tuition)
    //             if ($existingChallanStudent || $admissionWithTuition) {
    //                 return response()->json([
    //                     'error' => true,
    //                     'message' => 'Challan for this student has already been generated for this month.',
    //                 ]);
    //             }
    //         }

    //         if (!empty($request->class) && $request->class != 'all') {
    //             $query = $query->where('class_id', $request->input('class'));
    //         }

    //         $students = $query->get();

    //         // Resolve the Late Fee head once — reused for every student in the loop
    //         $lateFeeHead = FeeHead::where('fee_head', 'LATE FEE')->first();

    //         // ----------------------------------------------------------------
    //         // 4. Generate a challan per student
    //         // ----------------------------------------------------------------
    //         foreach ($students as $student) {
    //             // dd($student->id);
    //             $total = 0;
    //             $concession_amount = 0;
    //             $item = [];
    //             $itemIndex = 0;

    //             // Skip if a Regular or Advance challan already exists for this month
    //             $targetDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
    //             // $existingChallanStudent = Challans::where('student_id', $student->id)
    //             //     ->whereIn('challan_type', ['Regular', 'Advance', 'Admission'])
    //             //     ->where(function ($query) use ($targetDate) {

    //             //         $query->whereRaw(
    //             //             "STR_TO_DATE(fee_month, '%Y-%m-%d') = ?",
    //             //             [$targetDate]
    //             //         )
    //             //             ->orWhereRaw(
    //             //                 "other_months IS NOT NULL 
    //             //             AND FIND_IN_SET(?, REPLACE(other_months, ' ', ''))",
    //             //                 [$targetDate]
    //             //             );

    //             //     })
    //             //     ->exists();

    //              $existingChallanStudent = Challans::where('student_id', $student->id)
    //                 ->whereIn('challan_type', ['Regular', 'Advance'])
    //                 ->where(function ($query) use ($targetDate) {
    //                     $query->whereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') = ?", [$targetDate])
    //                         ->orWhereRaw("FIND_IN_SET(?, other_months)", [$targetDate]);
    //                 })
    //                 ->exists();

    //              $tuiton_fee = FeeHead::where('fee_head', 'LIKE', '%Tuition%')->first();
    //             $admissionWithTuition = false;
    //             if ($tuiton_fee) {
    //                 $admissionWithTuition = Challans::where('student_id', $student->id)
    //                     ->where('challan_type', 'Admission')
    //                     ->where(function ($query) use ($targetDate) {
    //                         $query->whereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') = ?", [$targetDate])
    //                             ->orWhereRaw("FIND_IN_SET(?, other_months)", [$targetDate]);
    //                     })
    //                     ->whereHas('heads', function ($q) use ($tuiton_fee) {
    //                         $q->where('head_id', $tuiton_fee->id); // ✅ FIXED COLUMN
    //                     })
    //                     ->exists();
    //             }



    //             if ($existingChallanStudent || $admissionWithTuition) {
    //                 continue;
    //             }

    //             // Skip if student was admitted after the fee month
    //             if ($student->enrollment->adm_date > $fee_month) {
    //                 continue;
    //             }

    //             // ---- Fetch fee structure and concession ----
    //             $fee_heads = StudentFeeStructure::with('feehead')
    //                 ->where('reg_id', $student->id)
    //                 ->where('checked_status', 1)
    //                 ->where('owned_by', $student->owned_by)
    //                 ->get();

    //             $concession = Concession::with('concession')
    //                 ->where('student_id', $student->id)
    //                 ->where('end_date', '>=', date('Y-m-d'))
    //                 ->orderBy('id', 'desc')
    //                 ->where('status', 'Approved')
    //                 ->first();

    //             if (!$concession) {
    //                 $concession = Concession::with('concession')
    //                     ->where('student_id', $student->id)
    //                     ->orderBy('id', 'desc')
    //                     ->whereNull('end_date')
    //                     ->where('status', 'Approved')
    //                     ->first();
    //             }

    //             // ---- Create the challan header ----
    //             $challan = new Challans;
    //             $challan->student_id = $student->id;
    //             $challan->rollno = $student->roll_no;
    //             $challan->class_id = $student->class_id;
    //             $challan->challanNo = $this->challanNo();
    //             $challan->challan_date = date('Y-m-d');
    //             $challan->fee_month = date('Y-m-01', strtotime($fee_month));
    //             $challan->challan_type = 'Regular';
    //             $challan->total_amount = 0;
    //             $challan->issue_date = $issueDate;
    //             $challan->due_date = $dueDate;
    //             $challan->status = 'Issued';
    //             $challan->owned_by = $student->owned_by;
    //             $challan->created_by = $student->created_by;
    //             $challan->session_id = $request->session;
    //             $challan->concession_id = $concession ? $concession->concession_id : '';
    //             $challan->concession_amount = 0;

    //             // ---- Populate other_months for multi-month subscriptions ----
    //             if ($duration > 1) {
    //                 $startDate = Carbon::parse($challan->fee_month);
    //                 $dates = [];
    //                 for ($i = 0; $i < $duration; $i++) {
    //                     $dates[] = $startDate->copy()->addMonths($i)->format('Y-m-d');
    //                 }
    //                 $challan->other_months = implode(',', $dates);
    //             } else {
    //                 $challan->other_months = null;
    //             }

    //             $challan->save();

    //             // ---- Process fee heads ----
    //             foreach ($fee_heads as $fee_head) {
    //                 $headNameLower = strtolower($fee_head->feehead->fee_head ?? '');

    //                 // Skip one-time heads that should never appear on a regular challan
    //                 if (
    //                     str_contains($headNameLower, 'security') ||
    //                     str_contains($headNameLower, 're-admission') ||
    //                     str_contains($headNameLower, 'transfer') ||
    //                     str_contains($headNameLower, 'admission') ||
    //                     str_contains($headNameLower, 'registration')
    //                 ) {
    //                     continue;
    //                 }

    //                 // Annual fee and late fee are always 1× (one-time / fixed charges)
    //                 $isOneTime = str_contains($headNameLower, 'annual fee')
    //                     || str_contains($headNameLower, 'late fee');
    //                 $multiplier = $isOneTime ? 1 : $duration;

    //                 $baseAmount = $fee_head->amount * $multiplier;

    //                 // Concession calculation
    //                 if ($concession) {
    //                     $conession_head = ConcessionPolicyHead::where('head_id', $fee_head->head_id)
    //                         ->where('concession_id', $concession->concession_id)
    //                         ->first();

    //                     $concessionAmount = $conession_head
    //                         ? round(($baseAmount / 100) * $conession_head->percentage)
    //                         : round(($baseAmount / 100) * $fee_head->discount);
    //                 } else {
    //                     $concessionAmount = round(($baseAmount / 100) * $fee_head->discount);
    //                 }

    //                 $total += $baseAmount;
    //                 $concession_amount += $concessionAmount ?? 0;

    //                 $challan_head = new ChallanHead;
    //                 $challan_head->challan_id = $challan->id ?? 0;
    //                 $challan_head->head_id = $fee_head->head_id;
    //                 $challan_head->price = $baseAmount;
    //                 $challan_head->concession = $concessionAmount ?? 0;
    //                 $challan_head->save();

    //                 $item[$itemIndex]['prod_id'] = $challan_head->id;
    //                 $item[$itemIndex]['head'] = $fee_head->head_id;
    //                 $item[$itemIndex]['price'] = $baseAmount;
    //                 $item[$itemIndex]['quantity'] = 1;
    //                 $item[$itemIndex]['concession'] = $concessionAmount ?? 0;
    //                 $item[$itemIndex]['total'] = $total;
    //                 $itemIndex++;
    //             }

    //             // ---- Update challan totals ----
    //             $challan->concession_amount = $concession_amount;
    //             $challan->total_amount = $total;
    //             $challan->save();

    //             // ---- Create journal entry ----
    //             $data = [];
    //             $data['id'] = $challan->id;
    //             $data['no'] = $challan->challanNo;
    //             $data['date'] = $challan->challan_date;
    //             $data['reference'] = $challan->student_id;
    //             $data['category'] = 'Regular';
    //             $data['user_id'] = $student->id;
    //             $data['user_type'] = 'Student';
    //             $data['owned_by'] = $challan->owned_by;
    //             $data['created_by'] = $challan->created_by;
    //             $data['items'] = $item;

    //             $dataret = Utility::jrentry($data);
    //             $challan->voucher_id = $dataret;
    //             $challan->save();

    //             $gen_count++;

    //             // ----------------------------------------------------------------
    //             // 5. Late fee attachment for previously ISSUED overdue challans
    //             //
    //             //    Now that the new challan is committed, scan every other
    //             //    "Issued" challan for this student.  If the challan's due date
    //             //    has passed today and it has no late fee head yet, attach one.
    //             //
    //             //    Rules (same as calculateAndUpdateLateFee / import logic):
    //             //      • Weekend-adjust the due date (Sat → Mon, Sun → Mon)
    //             //      • Days overdue capped at 10
    //             //      • 120 per day  →  maximum 1200
    //             //      • Two journal lines: Income (credit) + Receivable (debit)
    //             //      • Challan total_amount is increased accordingly
    //             // ----------------------------------------------------------------
    //             if ($lateFeeHead) {

    //                 $issuedChallans = Challans::where('student_id', $student->id)
    //                     ->whereRaw('LOWER(status) = ?', ['issued'])
    //                     ->whereNotIn('challan_type', ['Registration', 'Withdrawal', 'Transfer'])
    //                     ->where('id', '!=', $challan->id) // exclude the challan we just created
    //                     ->get();

    //                 foreach ($issuedChallans as $oldChallan) {

    //                     // --- Weekend-adjust due date (mirrors calculateAndUpdateLateFee) ---
    //                     $oldDueDate = Carbon::parse($oldChallan->due_date);
    //                     if ($oldDueDate->isSaturday()) {
    //                         $oldDueDate->addDays(2);
    //                     } elseif ($oldDueDate->isSunday()) {
    //                         $oldDueDate->addDay();
    //                     }

    //                     // Not overdue yet — nothing to do
    //                     if ($today->lte($oldDueDate)) {
    //                         continue;
    //                     }

    //                     // Already has a late fee head — skip to avoid duplicates
    //                     $alreadyHasLateFee = ChallanHead::where('challan_id', $oldChallan->id)
    //                         ->where('head_id', $lateFeeHead->id)
    //                         ->exists();

    //                     if ($alreadyHasLateFee) {
    //                         continue;
    //                     }

    //                     // Days overdue capped at 10 → max late fee = 1200
    //                     $daysOverdue = min($today->diffInDays($oldDueDate), 10);
    //                     $lateFeeAmount = $daysOverdue * 120;

    //                     if ($lateFeeAmount <= 0) {
    //                         continue;
    //                     }

    //                     // ---- Attach ChallanHead for late fee ----
    //                     $lateFeeHeadRecord = ChallanHead::create([
    //                         'challan_id' => $oldChallan->id,
    //                         'head_id' => $lateFeeHead->id,
    //                         'price' => $lateFeeAmount,
    //                         'concession' => 0,
    //                         'paid' => 0,
    //                     ]);

    //                     // ---- Journal: Income entry (credit side) ----
    //                     JournalItem::create([
    //                         'journal' => $oldChallan->voucher_id,
    //                         'account' => $lateFeeHead->account_id,
    //                         'head' => $lateFeeHead->id,
    //                         'entry_id' => $lateFeeHeadRecord->id,
    //                         'user_id' => $oldChallan->student_id,
    //                         'user_type' => 'Student',
    //                         'types' => 'Challan',
    //                         'credit' => $lateFeeAmount,
    //                         'debit' => 0,
    //                         'description' => 'Late Fee Income - Challan No ' . $oldChallan->challanNo,
    //                     ]);

    //                     // ---- Journal: Receivable entry (debit side) ----
    //                     JournalItem::create([
    //                         'journal' => $oldChallan->voucher_id,
    //                         'account' => $lateFeeHead->receivable_account_id,
    //                         'head' => $lateFeeHead->id,
    //                         'entry_id' => $lateFeeHeadRecord->id,
    //                         'user_id' => $oldChallan->student_id,
    //                         'user_type' => 'Student',
    //                         'types' => 'Challan',
    //                         'credit' => 0,
    //                         'debit' => $lateFeeAmount,
    //                         'description' => 'Late Fee Receivable - Challan No ' . $oldChallan->challanNo,
    //                     ]);

    //                     // ---- Bump the old challan's total ----
    //                     $oldChallan->total_amount += $lateFeeAmount;
    //                     $oldChallan->save();

    //                     \Log::info('Late fee attached to issued challan during bulk generate', [
    //                         'old_challan_id' => $oldChallan->id,
    //                         'old_challan_no' => $oldChallan->challanNo,
    //                         'student_id' => $student->id,
    //                         'days_overdue' => $daysOverdue,
    //                         'late_fee_amount' => $lateFeeAmount,
    //                     ]);
    //                 }
    //             }
    //             // ----------------------------------------------------------------
    //             // End late fee section
    //             // ----------------------------------------------------------------
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => $gen_count . ' Bulk Challan(s) generated successfully.',
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         dd($e);

    //         return response()->json(['error' => true, 'message' => 'An unexpected error occurred.']);
    //     }
    // }
public function paidchallan(Request $request)
    {
        // dd($request->all());
        \DB::beginTransaction();
        $data = [];
        try {
            $invoicePayment = Challans::where('challanNo', $request->challan_id)->first();

            // Fix due date if it falls on Sunday
            $dueDate = \Carbon\Carbon::parse($invoicePayment->due_date);
            if ($dueDate->isSunday()) {
                $invoicePayment->due_date = $dueDate->addDay()->format('Y-m-d');
                $invoicePayment->save();
                $invoicePayment->refresh(); // reload updated model
            }
            $isfiftypercent = false;
            if ($invoicePayment->paid_amount >= ($invoicePayment->total_amount - $invoicePayment->concession_amount) / 2) {
                $isfiftypercent = true;
            }
            if (!$isfiftypercent && $invoicePayment->student->register_option != 2) {
                $this->calculateAndUpdateLateFee($invoicePayment, $request->recipt_date);
            }
            // dd($invoicePayment,$invoicePayment->student);
            $invoicePayment->paid_date = $request->recipt_date;
            $invoicePayment->save();
            $item = [];
            $total = 0;
            $itemIndex = 0;
            for ($i = 0; $i < count($request->head_id); $i++) {
                if ($request->ramount[$i] != null && $request->ramount[$i] != 0) {
                    $total += $request->ramount[$i];
                    $item[$itemIndex]['head'] = $request->head_id[$i];
                    $item[$itemIndex]['price'] = $request->ramount[$i] ? $request->ramount[$i] : 0;
                    $item[$itemIndex]['quantity'] = 1;
                    $item[$itemIndex]['concession'] = 0;
                    $item[$itemIndex]['total'] = $total;
                    $itemIndex++;

                    $challan_head = ChallanHead::where('head_id', $request->head_id[$i])->where('challan_id', $invoicePayment->id)->first();
                    $challan_head->paid = $request->ramount[$i] + $challan_head->paid;
                    $challan_head->save();
                }
            }

            $invoicePayment->paid_amount = $invoicePayment->paid_amount + $total;
            $invoicePayment->save();

            $challan = Challans::where('challanNo', $request->challan_id)->first();
            $paidamount = $challan->paid_amount;
            $totalamount = $challan->total_amount;
            $concessionamount = $challan->concession_amount;
            $dueamount = $totalamount - ($paidamount + $concessionamount);
            if ($dueamount == 0) {
                $challan->status = 'Paid';
                $challan->save();
            } elseif ($dueamount < $totalamount) {
                $challan->status = 'Partial Paid';
                $challan->save();
            } else {
                $challan->status = 'Issued';
                $challan->save();
            }

            $timestamp = strtotime(str_replace('/', '-', $request->recipt_date));
            $reciptdate = date('Y-m-d', $timestamp);
            // dd($challan);
            if ($challan->student_id == null) {
                return response()->json(['error' => 'Student Registration not found'], 400);
            }
            // dd($challan->student_id);
            $recipts = StudentReceipt::create(
                [
                    'recipt_date' => $reciptdate,
                    'challan_id' => $challan->id,
                    'recipt_amount' => $total,
                    'student_id' => $challan->student_id,
                    'challan_amount' => $request->challan_amt,
                    'late_amount' => $request->late_amt,
                    'arrears' => $request->arrears,
                    'bank_id' => $request->bank,
                    'referance' => $request->ref,
                    'receive_type' => $request->receive_type,
                    'received_by' => Auth::user()->id,
                    'owned_by' => $challan->owned_by,
                    'created_by' => \Auth::user()->creatorId(),
                ]
            );

            Utility::bankAccountBalance($request->account_id, $request->amount, 'credit');
            $invoicePayment = Challans::where('challanNo', $request->challan_id)->first();

            $bankAccount = BankAccount::find($request->bank);
            if ($bankAccount->chart_account_id == '' || $bankAccount->chart_account_id == null || $bankAccount->chart_account_id == 0) {
                return response()->json(['error' => 'Bank Account Does not have Chart of Account attached.'], 400);
            }
            $data['id'] = $invoicePayment->id;
            $data['challan_id'] = $invoicePayment->id;
            $data['no'] = $invoicePayment->challanNo;
            $data['prod_id'] = $recipts->id;
            $data['date'] = $invoicePayment->paid_date;
            $data['reference'] = $recipts->referance;
            $data['description'] = $invoicePayment->description;
            $data['user_id'] = $challan->student_id;
            $data['bank_id'] = $request->bank;
            $data['bank_name'] = $bankAccount->bank_name;
            $data['branch_id'] = $challan->owned_by;
            $data['user_type'] = 'Student';
            $data['amount'] = $invoicePayment->amount;
            $data['category'] = $invoicePayment->challan_type;
            $data['owned_by'] = $challan->owned_by;
            $data['created_by'] = \Auth::user()->creatorId();
            $data['account_id'] = $bankAccount->chart_account_id;
            $data['recipt'] = $recipts->id;
            $data['receipt_date'] = $recipts->recipt_date;
            $data['created_at'] = $recipts->recipt_date . ' ' . date('H:i:s');
            $data['items'] = $item;
            $data['total'] = $total;
            // $dataret = Utility::brv_entry($data);

            if (ucwords($request->receive_type) == 'CD') {
                $dataret = Utility::crv_entry($data);
            } else {

                $dataret = Utility::brv_entry($data);
            }
            if ($invoicePayment->challan_type == 'Admission') {
                $Enroll = StudentEnrollments::where('regId', $invoicePayment->student_id)->first();
                if ($Enroll) {
                } else {
                    $registration = StudentRegistration::findOrFail($invoicePayment->student_id);
                    $section = ClassSection::where('class_id', $registration->class_id)->first();
                    // $prevEnrollId = StudentEnrollments::max('enrollId');
                    $prevEnrollId = StudentEnrollments::orderByDesc('enrollId')->value('enrollId');
                    $newEnrollId = $prevEnrollId ? $prevEnrollId + 1 : 1;
                    $enrollment = new StudentEnrollments;
                    $enrollment->enrollId = $newEnrollId;
                    $enrollment->regId = $registration->id;
                    $enrollment->class_id = $registration->class_id;
                    $enrollment->adm_date = $invoicePayment->paid_date;
                    $enrollment->section_id = @$section->section_id ? @$section->section_id : '';
                    $enrollment->session_id = $registration->session_id;
                    $enrollment->adm_session = $registration->session_id;
                    $enrollment->adm_branch = $registration->adm_branch;
                    $enrollment->owned_by = $challan->owned_by;
                    $enrollment->created_by = \Auth::user()->creatorId();
                    $enrollment->save();
                    $registration->roll_no = $newEnrollId;
                    $registration->student_status = 'Enrolled';
                    $registration->save();

                    $invoicePayment->rollno = $newEnrollId;
                    $invoicePayment->save();
                    // $recipts->student_id = $newEnrollId;
                    // $recipts->save();

                    if ($registration->fathercnic != null) {
                        $ischild = Employee::where('cnic', $registration->fathercnic)->first();
                        if ($ischild) {
                            // student_id is id
                            $empCh = new EmpChildrens;
                            $empCh->emp_id = $ischild->id;
                            $empCh->student_id = $registration->id;
                            $empCh->branch_id = $registration->branch;
                            $empCh->class_id = $registration->class_id;
                            $empCh->amount = 0;
                            $empCh->save();
                        }
                    }
                }
            } else if ($invoicePayment->challan_type == 'Readmission') {
                $Enroll = StudentEnrollments::where('regId', $invoicePayment->student_id)->first();
                if ($Enroll) {
                    $Enroll->adm_date = $invoicePayment->paid_date;
                    $Enroll->save();
                }
            }
            if (\Auth::user()->type == 'company') {
                $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            } else {
                $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }
            // dd($recipts);
            \DB::commit();
            $data = view('students.studentreceipt.data_row', compact('recipts', 'accounts'))->render();

            return response()->json(['success' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            \DB::rollback();
            dd($e);

            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
   //  public function paidchallan(Request $request)
   //  {
   //      // dd($request->all());
   //      \DB::beginTransaction();
   //      $data = [];
   //      try {
   //          $invoicePayment = Challans::where('challanNo', $request->challan_id)->first();

   //          // Fix due date if it falls on Sunday
   //          $dueDate = \Carbon\Carbon::parse($invoicePayment->due_date);
   //          if ($dueDate->isSunday()) {
   //              $invoicePayment->due_date = $dueDate->addDay()->format('Y-m-d');
   //              $invoicePayment->save();
   //              $invoicePayment->refresh(); // reload updated model
   //          }
   //          if (!$invoicePayment->paid_date && $invoicePayment->student->register_option != 2) {
   //              $this->calculateAndUpdateLateFee($invoicePayment, $request->recipt_date);
   //              }
   //              // dd($invoicePayment,$invoicePayment->student);
   //          $invoicePayment->paid_date = $request->recipt_date;
   //          $invoicePayment->save();
   //          $item = [];
   //          $total = 0;
   //          $itemIndex = 0;
   //          for ($i = 0; $i < count($request->head_id); $i++) {
   //              if ($request->ramount[$i] != null && $request->ramount[$i] != 0) {
   //                  $total += $request->ramount[$i];
   //                  $item[$itemIndex]['head'] = $request->head_id[$i];
   //                  $item[$itemIndex]['price'] = $request->ramount[$i] ? $request->ramount[$i] : 0;
   //                  $item[$itemIndex]['quantity'] = 1;
   //                  $item[$itemIndex]['concession'] = 0;
   //                  $item[$itemIndex]['total'] = $total;
   //                  $itemIndex++;

   //                  $challan_head = ChallanHead::where('head_id', $request->head_id[$i])->where('challan_id', $invoicePayment->id)->first();
   //                  $challan_head->paid = $request->ramount[$i] + $challan_head->paid;
   //                  $challan_head->save();
   //              }
   //          }

   //          $invoicePayment->paid_amount = $invoicePayment->paid_amount + $total;
   //          $invoicePayment->save();

   //          $challan = Challans::where('challanNo', $request->challan_id)->first();
   //          $paidamount = $challan->paid_amount;
   //          $totalamount = $challan->total_amount;
   //          $concessionamount = $challan->concession_amount;
   //          $dueamount = $totalamount - ($paidamount + $concessionamount);
   //          if ($dueamount == 0) {
   //              $challan->status = 'Paid';
   //              $challan->save();
   //          } elseif ($dueamount < $totalamount) {
   //              $challan->status = 'Partial Paid';
   //              $challan->save();
   //          } else {
   //              $challan->status = 'Issued';
   //              $challan->save();
   //          }

   //          $timestamp = strtotime(str_replace('/', '-', $request->recipt_date));
   //          $reciptdate = date('Y-m-d', $timestamp);
   //          // dd($challan);
   //          if ($challan->student_id == null) {
   //              return response()->json(['error' => 'Student Registration not found'], 400);
   //          }
   //          // dd($challan->student_id);
   //          $recipts = StudentReceipt::create(
   //              [
   //                  'recipt_date' => $reciptdate,
   //                  'challan_id' => $challan->id,
   //                  'recipt_amount' => $total,
   //                  'student_id' => $challan->student_id,
   //                  'challan_amount' => $request->challan_amt,
   //                  'late_amount' => $request->late_amt,
   //                  'arrears' => $request->arrears,
   //                  'bank_id' => $request->bank,
   //                  'referance' => $request->ref,
   //                  'receive_type' => $request->receive_type,
   //                  'received_by' => Auth::user()->id,
   //                  'owned_by' => $challan->owned_by,
   //                  'created_by' => \Auth::user()->creatorId(),
   //              ]
   //          );

   //          Utility::bankAccountBalance($request->account_id, $request->amount, 'credit');
   //          $invoicePayment = Challans::where('challanNo', $request->challan_id)->first();

   //          $bankAccount = BankAccount::find($request->bank);
   //          if ($bankAccount->chart_account_id == '' || $bankAccount->chart_account_id == null || $bankAccount->chart_account_id == 0) {
   //              return response()->json(['error' => 'Bank Account Does not have Chart of Account attached.'], 400);
   //          }
   //          $data['id'] = $invoicePayment->id;
   //          $data['challan_id'] = $invoicePayment->id;
   //          $data['no'] = $invoicePayment->challanNo;
   //          $data['prod_id'] = $recipts->id;
   //          $data['date'] = $invoicePayment->paid_date;
   //          $data['reference'] = $recipts->referance;
   //          $data['description'] = $invoicePayment->description;
   //          $data['user_id'] = $challan->student_id;
   //          $data['bank_id'] = $request->bank;
   //          $data['bank_name'] = $bankAccount->bank_name;
   //          $data['branch_id'] = $challan->owned_by;
   //          $data['user_type'] = 'Student';
   //          $data['amount'] = $invoicePayment->amount;
   //          $data['category'] = $invoicePayment->challan_type;
   //          $data['owned_by'] = $challan->owned_by;
   //          $data['created_by'] = \Auth::user()->creatorId();
   //          $data['account_id'] = $bankAccount->chart_account_id;
   //          $data['recipt'] = $recipts->id;
   //          $data['receipt_date'] = $recipts->recipt_date;
   //          $data['created_at'] = $recipts->recipt_date . ' ' . date('H:i:s');
   //          $data['items'] = $item;
   //          $data['total'] = $total;
   //          // $dataret = Utility::brv_entry($data);

   //          if (ucwords($request->receive_type) == 'CD') {
   //              $dataret = Utility::crv_entry($data);
   //          } else {

   //              $dataret = Utility::brv_entry($data);
   //          }
   //          if ($invoicePayment->challan_type == 'Admission') {
   //              $Enroll = StudentEnrollments::where('regId', $invoicePayment->student_id)->first();
   //              if ($Enroll) {
   //              } else {
   //                  $registration = StudentRegistration::findOrFail($invoicePayment->student_id);
   //                  $section = ClassSection::where('class_id', $registration->class_id)->first();
   //                  // $prevEnrollId = StudentEnrollments::max('enrollId');
   //                  $prevEnrollId = StudentEnrollments::orderByDesc('enrollId')->value('enrollId');
   //                  $newEnrollId = $prevEnrollId ? $prevEnrollId + 1 : 1;
   //                  $enrollment = new StudentEnrollments;
   //                  $enrollment->enrollId = $newEnrollId;
   //                  $enrollment->regId = $registration->id;
   //                  $enrollment->class_id = $registration->class_id;
   //                  $enrollment->section_id = @$section->section_id ? @$section->section_id : '';
   //                  $enrollment->session_id = $registration->session_id;
   //                  $enrollment->adm_session = $registration->session_id;
			// 		$enrollment->adm_date = $invoicePayment->paid_date;
   //                  $enrollment->adm_branch = $registration->adm_branch;
   //                  $enrollment->owned_by = $challan->owned_by;
   //                  $enrollment->created_by = \Auth::user()->creatorId();
   //                  $enrollment->save();
   //                  $registration->roll_no = $newEnrollId;
   //                  $registration->student_status = 'Enrolled';
   //                  $registration->save();

   //                  $invoicePayment->rollno = $newEnrollId;
   //                  $invoicePayment->save();
   //                  // $recipts->student_id = $newEnrollId;
   //                  // $recipts->save();

   //                  if ($registration->fathercnic != null) {
   //                      $ischild = Employee::where('cnic', $registration->fathercnic)->first();
   //                      if ($ischild) {
   //                          // student_id is id
   //                          $empCh = new EmpChildrens;
   //                          $empCh->emp_id = $ischild->id;
   //                          $empCh->student_id = $registration->id;
   //                          $empCh->branch_id = $registration->branch;
   //                          $empCh->class_id = $registration->class_id;
   //                          $empCh->amount = 0;
   //                          $empCh->save();
   //                      }
   //                  }
   //              }
   //          }
			// else if($invoicePayment->challan_type == 'Readmission') {
   //              $Enroll = StudentEnrollments::where('regId', $invoicePayment->student_id)->first();
   //              if ($Enroll) {
   //                  $Enroll->adm_date = $invoicePayment->paid_date;
   //                  $Enroll->save();
   //              }
   //          }
   //          if (\Auth::user()->type == 'company') {
   //              $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
   //          } else {
   //              $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
   //          }
   //          // dd($recipts);
   //          \DB::commit();
   //          $data = view('students.studentreceipt.data_row', compact('recipts', 'accounts'))->render();

   //          return response()->json(['success' => 'success', 'data' => $data]);
   //      } catch (\Exception $e) {
   //          \DB::rollback();
   //          dd($e);

   //          return response()->json(['error' => 'Something went wrong'], 500);
   //      }
   //  }

      public function admissionchallanlist(Request $request)
{
    if (\Auth::user()->type == 'company') {
        $branches = User::where('type', '=', 'branch')
            ->where('created_by', \Auth::user()->creatorId())
            ->get()->pluck('name', 'id');

        $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        $branches->prepend('Select Branch', '');

        $query = challans::orderBy('id', 'Desc');

        $students = StudentRegistration::select(
            \DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'),
            'roll_no'
        )
        ->where('created_by', \Auth::user()->creatorId())
        ->where('student_status', '!=', 'Registered')
        ->get()->pluck('stdname', 'roll_no');

        $students->prepend('All Students', 'all');
    } else {
        $branches = User::where('id', '=', \Auth::user()->ownedId())
            ->get()->pluck('name', 'id');

        $branches->prepend('Select Branch', '');

        $query = challans::orderBy('id', 'Desc')
            ->where('owned_by', '=', \Auth::user()->ownedId());

        $students = StudentRegistration::select(
            \DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'),
            'roll_no'
        )
        ->where('owned_by', \Auth::user()->ownedId())
        ->where('student_status', '!=', 'Registered')
        ->get()->pluck('stdname', 'roll_no');

        $students->prepend('All Students', 'all');
    }

    $session = Session::where('created_by', \Auth::user()->creatorId())
        ->get()->pluck('year', 'id');

    $session->prepend('Select Session', '');

    // ✅ FIX START
    $class = collect();
    $class->prepend('All Class', 'all');

    $sections = collect();
    $sections->prepend('All Section', 'all');
    // ✅ FIX END

    if (!empty($request->branches)) {
        $query->where('owned_by', '=', $request->branches);

        $class = Classes::where('owned_by', '=', $request->branches)
            ->get()->pluck('name', 'id');

        $students = StudentRegistration::select(
            \DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'),
            'roll_no'
        )
        ->where('branch', '=', $request->branches)
        ->where('student_status', '!=', 'Registered')
        ->get()->pluck('stdname', 'roll_no');

        $students->prepend('All Students', 'all');

        $class->prepend('All Class', 'all');
    }

    if (! empty($request->session)) {
        $query->where('session_id', '=', $request->session);
    }

    if (! empty($request->class) && $request->class != 'all') {
        $query->where('class_id', '=', $request->class);

        $students = StudentRegistration::select(
            \DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'),
            'roll_no'
        )
        ->where('class_id', '=', $request->class)
        ->where('student_status', '!=', 'Registered')
        ->get()->pluck('stdname', 'roll_no');

        $students->prepend('All Students', 'all');
    }

    if (!empty($request->student) && $request->student != 'all') {
        $query->where('rollno', '=', $request->student);
    }
    if (!empty($request->challan_date)) {
        $challan_date = date('Y-m-d', strtotime($request->challan_date));
        $query->whereBetween('fee_month', [
            date('Y-m-01', strtotime($challan_date)),
            date('Y-m-t', strtotime($challan_date)),
        ]);
    } elseif (!empty($request->from_date) && ! empty($request->to_date)) {
        $query->whereBetween('challan_date', [$request->from_date, $request->to_date]);
    } else {
        $challan_date = date('Y-m-d');

        $query->whereBetween('fee_month', [
            date('Y-m-01', strtotime($challan_date)),
            date('Y-m-t', strtotime($challan_date)),
        ]);
    }
    
    $students->prepend('All Students', 'all');
    
    $pattern = '%admission%';
    $breadcrumb = 'Admission Challans';
    
    $challan_list = $query
    ->whereRaw('LOWER(challan_type) LIKE ?', [strtolower($pattern)])
    ->where('challan_type', 'NOT LIKE', '%readmission%')
    ->get();
    // dd($query->get(),$challan_list);

    $type = strtolower('admission');

    return view(
        'students.challanlists.adm_challan_list',
        compact('session', 'class', 'students', 'branches', 'sections', 'challan_list', 'breadcrumb', 'type')
    );
}

    public function registrationchallanlist(Request $request)
    {
        // dd($request->all());
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = challans::orderBy('id', 'Desc');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = challans::orderBy('id', 'Desc')->where('owned_by', '=', \Auth::user()->ownedId());
        }
        $session = Session::where('created_by', \Auth::user()->creatorId())->get()->pluck('year', 'id');
        $session->prepend('Select Session', '');
        $class = [];
        $students = StudentRegistration::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('stdname', 'id');
        $students->prepend('All Students', 'all');
        if (! empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            // $session = Session::where('owned_by', '=', $request->branches)->get()->pluck('year', 'id');
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            $students = StudentRegistration::where('branch', '=', $request->branches)->get()->pluck('stdname', 'id');
            $students->prepend('All Students', 'all');
        }
        if (! empty($request->session)) {
            $query->where('session_id', '=', $request->session);
        }
        if (! empty($request->class) && $request->class != 'all') {
            $query->where('class_id', '=', $request->class);
            $students = StudentRegistration::where('branch', '=', $request->branches)->get()->pluck('stdname', 'id');
            $students->prepend('All Students', 'all');
        }
        if (! empty($request->student) && $request->student != 'all') {
            $query->where('student_id', '=', $request->student);
        }
       // ✅ DATE FILTER LOGIC

if (!empty($request->from_date) && !empty($request->to_date)) {

    // Custom date range
    $query->whereBetween('challan_date', [
        date('Y-m-d', strtotime($request->from_date)),
        date('Y-m-d', strtotime($request->to_date))
    ]);

} elseif (!empty($request->challan_date)) {

    // Single month filter
    $query->whereBetween('challan_date', [
        date('Y-m-01', strtotime($request->challan_date)),
        date('Y-m-t', strtotime($request->challan_date))
    ]);

} else {

    // Default current month
    $currentDate = date('Y-m-d');

    $query->whereBetween('challan_date', [
        date('Y-m-01', strtotime($currentDate)),
        date('Y-m-t', strtotime($currentDate))
    ]);
}
        // dd($query->get());

        $pattern = '%Registration%';
        $breadcrumb = 'Registration Challans';
        $challan_list = $query->whereRaw('LOWER(challan_type) LIKE ?', [strtolower($pattern)])->get();
        $type = strtolower('Registration');

        //     return view('students.challanlists.adm_challan_list', compact('branches', 'session', 'class', 'challan_list', 'breadcrumb'));
        // }
        return view('students.challanlists.adm_challan_list', compact('session', 'class', 'students', 'branches', 'challan_list', 'breadcrumb', 'type'));
    }

   public function regularchallanlist(Request $request)
    {
        if (\Auth::user()->type == 'company') {

            $branches = User::where('type', '=', 'branch')
				->where('is_active', 1)
                ->where('created_by', \Auth::user()->creatorId())
                ->get()->pluck('name', 'id');

            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'all');

            $students = StudentRegistration::select(
                \DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'),
                'roll_no'
            )
                ->where('created_by', \Auth::user()->creatorId())
                ->where('student_status', '!=', 'Registered')
                ->get()->pluck('stdname', 'roll_no');

            $query = Challans::orderBy('id', 'Desc');
            $students->prepend('All Students', 'all');

        } else {

            $branches = User::where('id', '=', \Auth::user()->ownedId())
				->where('is_active', 1)
                ->get()->pluck('name', 'id');

            $branches->prepend('All Branches', 'all');

            $query = Challans::orderBy('id', 'Desc')
                ->where('owned_by', \Auth::user()->ownedId());

            $students = StudentRegistration::select(
                \DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'),
                'roll_no'
            )
                ->where('owned_by', \Auth::user()->ownedId())
                ->where('student_status', '!=', 'Registered')
                ->get()->pluck('stdname', 'roll_no');

            $students->prepend('All Students', 'all');
        }

        // ---------------- SESSION ----------------
        $session = Session::where('created_by', \Auth::user()->creatorId())
            ->get()->pluck('year', 'id');

        $session->prepend('Select Session', '');

        // ---------------- CLASS / SECTION ----------------
        $class = collect();
        $class->prepend('All Class', 'all');

        $sections = collect();
        $sections->prepend('All Section', 'all');

        // ---------------- MONTH FILTER FIX ----------------
        if (!empty($request->challan_date)) {
            $challan_date = date('Y-m-d', strtotime($request->challan_date));
        } else {
            $challan_date = date('Y-m-d');
        }
        $yearMonth = date('Y-m', strtotime($challan_date));
        $query->where(function ($q) use ($yearMonth) {

            $q->where(function ($q1) use ($yearMonth) {
                // normal challan month match
                $q1->where('fee_month', 'like', $yearMonth . '%');
            })

                ->orWhere(function ($q2) use ($yearMonth) {
                    // subscription months match (convert date format)
                    $q2->whereRaw("
            REPLACE(other_months, '-01', '') LIKE ?
        ", ["%$yearMonth%"]);
                });

        });

        // ---------------- BRANCH FILTER ----------------
        if (!empty($request->branches) && $request->branches != 'all') {

            $query->where('owned_by', '=', $request->branches);

            $class = Classes::where('owned_by', '=', $request->branches)
                ->get()->pluck('name', 'id');

            $class->prepend('All Class', 'all');

            $students = StudentRegistration::select(
                \DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'),
                'roll_no'
            )
                ->where('branch', '=', $request->branches)
                ->where('student_status', '!=', 'Registered')
                ->get()->pluck('stdname', 'roll_no');

            $students->prepend('All Students', 'all');
        }

        // ---------------- SESSION FILTER ----------------
        if (!empty($request->session)) {
            $query->where('session_id', '=', $request->session);
        }

        // ---------------- CLASS FILTER ----------------
        // ---------------- CLASS FILTER ----------------
if (!empty($request->class) && $request->class != 'all') {

    // Fetch challans directly by class_id inside challans table
    $query->where('class_id', '=', $request->class);

    // Fetch only students who have challans in this selected class
    $studentIds = Challans::where('class_id', $request->class)
        ->pluck('student_id')
        ->unique()
        ->toArray();

    $students = StudentRegistration::select(
            \DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'),
            'roll_no'
        )
        ->whereIn('id', $studentIds)
        ->where('student_status', '!=', 'Registered')
        ->get()
        ->pluck('stdname', 'roll_no');

    $students->prepend('All Students', 'all');

    $sections = \DB::table('class_sections')
        ->join('sections', 'class_sections.section_id', '=', 'sections.id')
        ->where('class_sections.class_id', $request->class)
        ->select('sections.id', 'sections.name')
        ->distinct()
        ->pluck('sections.name', 'sections.id');

    $sections->prepend('All Section', 'all');
}
        // ---------------- SECTION FILTER ----------------
        if (!empty($request->section) && $request->section != 'all') {
            $query->whereHas('student', function ($q) use ($request) {
                $q->whereHas('enrollment', function ($q2) use ($request) {
                    $q2->where('section_id', $request->section)
                        ->where('active_status', 1);
                });
            });
        }

        // ---------------- STUDENT FILTER ----------------
        if (!empty($request->student) && $request->student != 'all') {
            $stdreg = StudentRegistration::where('roll_no',$request->student)->first();
            $query->where('student_id', $stdreg->id);
        }
        // dd($query->get());

        // ---------------- CHALLAN TYPE FILTER ----------------
        $pattern = '%regular%';
        $pattern2 = '%Advance%';
        $breadcrumb = 'Regular Challans';

        $challan_list = $query
            ->where(function ($q) use ($pattern, $pattern2) {
                $q->whereRaw('LOWER(challan_type) LIKE ?', [strtolower($pattern)])
                    ->orWhereRaw('LOWER(challan_type) LIKE ?', [strtolower($pattern2)]);
            })
            ->get();

        $type = strtolower('regular');

        return view(
            'students.challanlists.adm_challan_list',
            compact('branches', 'students', 'session', 'class', 'sections', 'challan_list', 'breadcrumb', 'type')
        );
    }


    public function advancechallanlist(Request $request)
    {
		 $startDate = $request->from_date ? date('Y-m-d', strtotime($request->from_date)) : date('Y-m-01');
        $endDate = $request->to_date ? date('Y-m-d', strtotime($request->to_date)) : date('Y-m-t');
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('created_by', \Auth::user()->creatorId())->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $query = challans::orderBy('id', 'Desc');
            $students->prepend('All Students', 'all');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            // $branches->prepend('Select Branch', '');
            $query = challans::orderBy('id', 'Desc')->where('owned_by', '=', \Auth::user()->ownedId());
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('owned_by', \Auth::user()->ownedId())->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $students->prepend('All Students', 'all');
        }
        $session = Session::where('created_by', \Auth::user()->creatorId())->get()->pluck('year', 'id');
        $session->prepend('Select Session', '');
        $class = [];

        if (! empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            // $session = Session::where('owned_by', '=', $request->branches)->get()->pluck('year', 'id');
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('branch', '=', $request->branches)->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $students->prepend('All Students', 'all');
            // $session->prepend('Select Session', '');
            $class->prepend('Select Class', '');
        }
        if (! empty($request->session)) {
            $query->where('session_id', '=', $request->session);
        }
        if (! empty($request->class)) {
            $query->where('class_id', '=', $request->class);
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('class_id', '=', $request->class)->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $students->prepend('All Students', 'all');
        }
        if (! empty($request->student) && $request->student != 'all') {
            $query->where('rollno', '=', $request->student);
        }
        // if (! empty($request->challan_date)) {
        //     $challan_date = date('Y-m-d', strtotime($request->challan_date));
        //     $query->whereBetween('fee_month', [
        //         date('Y-m-01', strtotime($challan_date)), // First day of the month
        //         date('Y-m-t', strtotime($challan_date)),   // Last day of the month
        //     ]);
        //     // $query->whereYear('fee_month', date('Y', strtotime($request->challan_date)))->whereMonth('fee_month', date('m', strtotime($request->challan_date)));
        // } else {
        //     $challan_date = date('Y-m-d');
        //     $query->whereBetween('fee_month', [
        //         date('Y-m-01', strtotime($challan_date)), // First day of the month
        //         date('Y-m-t', strtotime($challan_date)),   // Last day of the month
        //     ]);

        // }
		 $query->whereBetween('fee_month', [$startDate, $endDate]);
        $pattern = '%advance%';
        $breadcrumb = 'Advance Challans';

        $challan_list = $query->whereRaw('LOWER(challan_type) LIKE ?', [strtolower($pattern)])->get();
        $type = strtolower('advance');

        return view('students.challanlists.adm_challan_list', compact('branches', 'students', 'session', 'class', 'challan_list', 'breadcrumb', 'type'));
    }

    public function createChallan(Request $request)
    {
        $session = Session::where('created_by', \Auth::user()->creatorId())->get()->pluck('year', 'id');
        $session->prepend('Select Session', '');
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'all');
            $branches->prepend('Select Branch', '');

        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('All Branches', 'all');
            $branches->prepend('Select Branch', '');
        }
        $challanType = $request->type;

        return view('students.challanlists.create', compact('branches', 'challanType', 'session'));
    }

public function storeChallan(Request $request)
    {
        // dd($request->all());
        if ($request->has('challan_type')) {
            $clean = preg_replace('/\s*challan\s*$/i', '', $request->input('challan_type'));
            $request->merge(['challan_type' => strtolower($clean)]);
        }
        $rules = [
            'session_id' => 'required',
            'branch_id' => 'required',
            'class_id' => 'required',
            'student_id' => 'required',
            'challan_type' => 'required|in:advance,regular',
            'advance_months' => 'required_if:challan_type,advance|integer|min:1',
            'fee_month' => 'required|date_format:Y-m',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
        ];

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            dd($messages->first());

            return redirect()->back()->with('error', $messages->first());
        }
        $chType = str_replace(' CHALLAN', '', $request->challan_type);
        $type = strtolower($chType);
        $feeMonth = $request->fee_month;
        $advanceMonths = (int) $request->advance_months;
        $issueDate = $request->issue_date;
        $dueDate = $request->due_date;

        if ($type == 'advance' && $request->student_id == 'all') {
            return redirect()->back()->with('error', 'Advance challan must be for a single student');
        }

        // build other_months string for advance
        $otherMonths = '';
        if ($type == 'advance') {
            $baseTs = strtotime("$feeMonth-01");
            $dates = [];
            for ($i = 0; $i < $advanceMonths; $i++) {
                $dates[] = date('Y-m-d', strtotime("+{$i} month", $baseTs));
            }
            $otherMonths = implode(',', $dates);
        }
        // dd($otherMonths,$feeMonth);
        if ($type == 'regular') {
            $query = StudentRegistration::query()
                ->with('enrollment')
                ->where('student_status', 'Enrolled')
                ->where('session_id', $request->session_id);
            if ($request->branch_id != 'all') {
                $query->where('owned_by', $request->branch_id);
            }
            if ($request->class_id != 'all') {
                $query->where('class_id', $request->class_id);
            }
            if ($request->student_id != 'all') {
                $query->where('roll_no', $request->student_id);
            }
            $students = $query->get();
            if ($students->isEmpty()) {
                return redirect()->back()->with('error', 'No students found for the selected criteria.');
            }
        } else {
            $students = StudentRegistration::with('enrollment')
                ->where('roll_no', $request->student_id)
                ->where('student_status', 'Enrolled')
                ->where('session_id', $request->session_id)
                ->get();
            // $students = collect([
            //     StudentRegistration::where('roll_no', $request->student_id)
            //         ->firstOrFail()
            // ]);
        }

        DB::beginTransaction();
        try {
            $created = 0;
            $skipped = [];

            foreach ($students as $student) {
                if ($type == 'advance') {
                    $existingMonths = Challans::where('student_id', $student->id)
                        ->pluck('other_months')
                        ->filter()
                        ->flatMap(fn($otherMonths) => explode(',', $otherMonths))
                        ->map(fn($month) => trim($month))
                        ->unique()
                        ->toArray();

                    $conflicts = array_intersect($existingMonths, $dates);

                    if (!empty($conflicts)) {
                        $conflictMonths = array_map(fn($month) => date('M-Y', strtotime($month)), $conflicts);

                        DB::rollBack();

                        return redirect()->back()->with(
                            'error',
                            'Challan already exists for: ' . implode(', ', $conflictMonths)
                        );
                    }
                }

                // regular: skip if exists
                if ($type == 'regular') {
                    $exists = Challans::where('student_id', $student->id)
                        ->where('challan_type', 'Regular')
                        ->orWhere('challan_type', 'Advance')
                        ->where('fee_month', date('Y-m-01', strtotime($feeMonth)))
                        ->orWhere('other_months', 'LIKE', '%' . date('Y-m-d', strtotime($feeMonth)) . '%')
                        ->exists();

                    if ($exists) {
                        $skipped[] = $student->roll_no;

                        continue;
                    }
                }

                $billingMonths = $type == 'advance'
                    ? $dates
                    : [date('Y-m-01', strtotime($feeMonth))];

                $chargeableMonthCount = collect($billingMonths)
                    ->filter(fn($month) => !$this->appliesJunJulFeeExemption($student, Carbon::parse($month)))
                    ->count();

                // fetch fee heads + concession
                $feeHeads = StudentFeeStructure::with('feehead')
                    ->where('reg_id', $student->id)
                    ->where('checked_status', 1)
                    ->where('owned_by', $student->owned_by)
                    ->get();
                if ($feeHeads->isEmpty()) {
                    // dd($feeHeads);
                    $skipped[] = $student->roll_no;

                    continue;
                }
                $concession = Concession::where('student_id', $student->id)
                    ->where(function ($q) {
                        $q->where('end_date', '>=', now())
                            ->orWhereNull('end_date');
                    })
                    ->where('status', 'Approved')
                    ->latest()
                    ->first();

                // create challan record
                $challan = Challans::create([
                    'student_id' => $student->id,
                    'rollno' => $student->roll_no,
                    'class_id' => $student->class_id,
                    'concession_id' => optional($concession)->concession_id,
                    'challanNo' => $this->challanNo(),
                    'challan_date' => now()->toDateString(),
                    'fee_month' => date('Y-m-01', strtotime($feeMonth)),
                    'challan_type' => ucfirst($type),
                    'other_months' => $otherMonths,
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate,
                    'session_id' => $request->session_id,
                    'owned_by' => $student->owned_by,
                    'created_by' => \Auth::user()->creatorId(),
                ]);
                $totalAmt = 0;
                $totalConces = 0;
                $annualAdded = false;

                foreach ($feeHeads as $fh) {

                    $headName = strtolower($fh->feehead->fee_head ?? '');

                    $isAdmissionOrSecurity =
                        str_contains($headName, 'security')
                        || str_contains($headName, 're-admission')
                        || str_contains($headName, 'transfer')
                        || str_contains($headName, 'admission')
                        || str_contains($headName, 'registration');

                    // If challan type is NOT admission, skip Admission and Security heads
                    if ($type !== 'admission' && $isAdmissionOrSecurity) {
                        continue;
                    }

                    $isAnnual = str_contains($headName, 'annual');

                    // skip if annual already added
                    if ($isAnnual && $annualAdded) {
                        continue;
                    }

                    if ($isAnnual) {
                        $qty = $chargeableMonthCount > 0 ? 1 : 0;
                        $annualAdded = true;
                    } else {
                        $qty = $chargeableMonthCount;
                    }

                    $price = $fh->amount * $qty;

                    $pct = optional(
                        ConcessionPolicyHead::where('head_id', $fh->head_id)
                            ->where('concession_id', optional($concession)->concession_id)
                            ->first()
                    )->percentage ?? $fh->discount;

                    $con = round(($fh->amount / 100) * $pct) * $qty;

                    $totalAmt += $price;
                    $totalConces += $con;

                    ChallanHead::create([
                        'challan_id' => $challan->id,
                        'head_id' => $fh->head_id,
                        'price' => $price,
                        'concession' => $con,
                    ]);
                }
                // dd('');
                $challan->update([
                    'total_amount' => $totalAmt,
                    'concession_amount' => $totalConces,
                ]);

                $jrData = [
                    'id' => $challan->id,
                    'no' => $challan->challanNo,
                    'date' => $challan->challan_date,
                    'reference' => $challan->student_id,
                    'category' => ucfirst(string: $type),
                    'user_id' => $student->id,
                    'user_type' => 'Student',
                    'owned_by' => $challan->owned_by,
                    'created_by' => $challan->created_by,
                    'items' => $challan->heads->map(fn($h) => [
                        'prod_id' => $h->id,
                        'head' => $h->head_id,
                        'price' => $h->price,
                        'quantity' => 1,
                        'concession' => $h->concession,
                        'total' => $h->price,
                    ])->toArray(),
                ];
                $jr = Utility::jrentry($jrData);
                // dd($jr);
                $challan->voucher_id = $jr;
                $challan->save();
                // $challan->update(['voucher_id' => $jr]);
                $created++;
            }

            DB::commit();

            return redirect()->back()->with(
                'success',
                "Created: {$created} challans Successfully." .
                ($skipped ? ', Skipped: ' . implode(', ', $skipped) : '')
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e);
            dd($e);

            return redirect()->back()->with('error', 'Server error: ' . $e->getMessage());
        }
    }




    public function readmissionchallanlist(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('created_by', \Auth::user()->creatorId())->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $students->prepend('All Students', 'all');
            $query = challans::orderBy('id', 'Desc');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('owned_by', \Auth::user()->ownedId())->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $students->prepend('All Students', 'all');
            $query = challans::orderBy('id', 'Desc');
        }
        $session = Session::where('created_by', \Auth::user()->creatorId())->get()->pluck('year', 'id');
        $session->prepend('Select Session', '');

        if (! empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            // $session = Session::where('owned_by', '=', $request->branches)->get()->pluck('year', 'id');
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('branch', '=', $request->branches)->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $students->prepend('All Students', 'all');
            // $session->prepend('Select Session', '');
            $class->prepend('Select Class', '');
        }
        if (! empty($request->session)) {
            $query->where('session_id', '=', $request->session);
        }
        if (! empty($request->class) && $request->class != 'all') {
            $query->where('class_id', '=', $request->class);
        }
        if (! empty($request->student) && $request->student != 'all') {
            $query->where('rollno', '=', $request->student);
        }
        // if (! empty($request->challan_date)) {
        //     $challan_date = date('Y-m-d', strtotime($request->challan_date));
        //     $query->whereBetween('fee_month', [
        //         date('Y-m-01', strtotime($challan_date)), // First day of the month
        //         date('Y-m-t', strtotime($challan_date)),   // Last day of the month
        //     ]);
        //     // $query->whereYear('fee_month', date('Y', strtotime($request->challan_date)))->whereMonth('fee_month', date('m', strtotime($request->challan_date)));
        // } else {
        //     $challan_date = date('Y-m-d');
        //     $query->whereBetween('fee_month', [
        //         date('Y-m-01', strtotime($challan_date)), // First day of the month
        //         date('Y-m-t', strtotime($challan_date)),   // Last day of the month
        //     ]);
        // }
        $class = [];
        $pattern = '%readmission%';
        $breadcrumb = 'Re-Admission Challans';
        $challan_list = $query->whereRaw('LOWER(challan_type) LIKE ?', [strtolower($pattern)])->get();
        $type = strtolower('readmission');

        return view('students.challanlists.adm_challan_list', compact('branches', 'students', 'session', 'class', 'challan_list', 'breadcrumb', 'type'));
    }

    public function transferchallanlist(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('created_by', \Auth::user()->creatorId())->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $students->prepend('All Students', 'all');
            $query = challans::orderBy('id', 'Desc');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('owned_by', \Auth::user()->ownedId())->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $students->prepend('All Students', 'all');
            $query = challans::orderBy('id', 'Desc');
        }
        $session = Session::where('created_by', \Auth::user()->creatorId())->get()->pluck('year', 'id');
        $session->prepend('Select Session', '');
        $class = [];
        if (! empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            // $session = Session::where('owned_by', '=', $request->branches)->get()->pluck('year', 'id');
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('branch', '=', $request->branches)->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $students->prepend('All Students', 'all');
        }
        if (! empty($request->session)) {
            $query->where('session_id', '=', $request->session);
        }
        if (! empty($request->class) && $request->class != 'all') {
            $query->where('class_id', '=', $request->class);
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('class_id', '=', $request->class)->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $students->prepend('All Students', 'all');
        }
        if (! empty($request->student) && $request->student != 'all') {
            $query->where('rollno', '=', $request->student);
        }
        if (! empty($request->challan_date)) {
            $challan_date = date('Y-m-d', strtotime($request->challan_date));
            $query->whereBetween('fee_month', [
                date('Y-m-01', strtotime($challan_date)), // First day of the month
                date('Y-m-t', strtotime($challan_date)),   // Last day of the month
            ]);
            // $query->whereYear('fee_month', date('Y', strtotime($request->challan_date)))->whereMonth('fee_month', date('m', strtotime($request->challan_date)));
            // dd($query->getBindings(),$query->toSql());
        } else {
            $challan_date = date('Y-m-d');
            $query->whereBetween('fee_month', [
                date('Y-m-01', strtotime($challan_date)), // First day of the month
                date('Y-m-t', strtotime($challan_date)),   // Last day of the month
            ]);
        }
        // dd();
        $pattern = '%transfer%';
        $breadcrumb = 'Transfer Challans';

        $challan_list = $query->whereRaw('LOWER(challan_type) LIKE ?', [strtolower($pattern)])->get();
        $type = strtolower('transfer');

        return view('students.challanlists.adm_challan_list', compact('branches', 'students', 'session', 'class', 'challan_list', 'breadcrumb', 'type'));
    }
    // $breadcrumb = 'Transfer Challans';

    public function installmentview(Request $request, $id)
    {

        $challan = Challans::where('id', $id)->where('status', 'Issued')->first();
        // dd($challan);
        if (! $challan) {
            return redirect()->back()->with('error', "Can't update this challan");
        }
        $studentData = StudentRegistration::with('class', 'branches')->where('id', @$challan->student_id)->first();
        $classfee = StudentFeeStructure::with('feehead')->where('reg_id', @$challan->student_id)->where('owned_by', $studentData->owned_by)->get();
        $concession = Concession::with('concession')
            ->where('student_id', @$challan->student_id)
            ->where('end_date', '>=', date('Y-m-d'))
            ->where('status', 'Approved')
            ->orderBy('id', 'desc')
            ->first();
        if (! $concession) {
            $concession = Concession::with('concession')
                ->where('student_id', @$challan->student_id)
                ->where('status', 'Approved')
                ->orderBy('id', 'desc')
                ->first();
        }

        $installmentChallans = Challans::with('heads')->where('student_id', $challan->student_id)
            ->where('challan_type', 'Admission')
            ->get();

        // Array of all heads records
        $allHeads = $installmentChallans->flatMap(function ($challan) {
            return $challan->heads;
        })->toArray();

        // Get all head IDs in one array
        $headIds = $installmentChallans->flatMap(function ($challan) {
            return $challan->heads->pluck('head_id');
        })->toArray();

        return view('students.challanlists.installmentview', compact('challan', 'studentData', 'classfee', 'concession', 'installmentChallans', 'allHeads', 'headIds'));
    }

    public function installmentviewreadmission(Request $request, $id)
    {

        $challan = Challans::where('id', $id)->where('status', 'Issued')->first();
        // dd($challan);
        if (! $challan) {
            return redirect()->back()->with('error', "Can't update this challan");
        }
        $studentData = StudentRegistration::with('class', 'branches')->where('id', @$challan->student_id)->first();
        $classfee = StudentFeeStructure::with('feehead')->where('reg_id', @$challan->student_id)->where('owned_by', $studentData->owned_by)->get();
        $concession = Concession::with('concession')
            ->where('student_id', @$challan->student_id)
            ->where('end_date', '>=', date('Y-m-d'))
            ->where('status', 'Approved')
            ->orderBy('id', 'desc')
            ->first();
        if (! $concession) {
            $concession = Concession::with('concession')
                ->where('student_id', @$challan->student_id)
                ->where('status', 'Approved')
                ->orderBy('id', 'desc')
                ->first();
        }

        $installmentChallans = Challans::with('heads')->where('student_id', $challan->student_id)
            ->where('challan_type', 'Readmission')
            ->get();

        // Array of all heads records
        $allHeads = $installmentChallans->flatMap(function ($challan) {
            return $challan->heads;
        })->toArray();

        // Get all head IDs in one array
        $headIds = $installmentChallans->flatMap(function ($challan) {
            return $challan->heads->pluck('head_id');
        })->toArray();

        return view('students.challanlists.installmentview', compact('challan', 'studentData', 'classfee', 'concession', 'installmentChallans', 'allHeads', 'headIds'));
    }

     public function installment_challan(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $existingChallan = Challans::with('heads', 'heads.feeHead')->findOrFail($id);
            $issueDate = Carbon::now()->toDateString();
            $dueDate = Carbon::now()->addWeek()->toDateString();
            $challanDate = Carbon::parse($existingChallan->challan_date);
            $feeHeadIds = $request->input('fee_head_id');
            $inst1Amounts = $request->input('head_amount_inst1');
            $inst2Amounts = $request->input('head_amount_inst2');
            $student = StudentRegistration::findOrFail($existingChallan->student_id);
            $challan = $existingChallan;
            $heads = $feeHeadIds;

            if ($request->type == 'view') {
                return view('challans.installchallan', compact('challan', 'heads', 'inst1Amounts', 'inst2Amounts', 'feeHeadIds'));
            }

            $totalInst1 = 0;
            $totalInst2 = 0;
            $feeHeadsInst1 = [];
            $feeHeadsInst2 = [];
            $concessionInst1 = 0;
            $concessionInst2 = 0;

            // ----------------------------------------------------------------
            // Helper: build jrentry-compatible item array from collected heads
            // ----------------------------------------------------------------
            $buildItems = function (array $feeHeads) {
                $items = [];
                foreach ($feeHeads as $feeHead) {
                    // Support both array (from flatMap) and Eloquent object
                    $isArray = is_array($feeHead);
                    $items[] = [
                        'prod_id' => $isArray ? ($feeHead['id'] ?? null) : ($feeHead->id ?? null),
                        'head' => $isArray ? $feeHead['head_id'] : $feeHead->head_id,
                        'price' => $isArray ? $feeHead['price'] : $feeHead->price,
                        'quantity' => 1,
                        'concession' => $isArray ? $feeHead['concession'] : $feeHead->concession,
                        'total' => $isArray
                            ? ($feeHead['price'] - $feeHead['concession'])
                            : ($feeHead->price - $feeHead->concession),
                    ];
                }
                return $items;
            };

            // ----------------------------------------------------------------
            // Helper: wipe existing heads + journal items for a challan,
            // re-insert heads, then call jrentry (which handles all JV lines
            // including concession discount entries — exactly as bulkchallan)
            // ----------------------------------------------------------------
            $rebuildChallan = function (Challans $challanModel, array $feeHeads, float $total, float $concession, string $category = 'Admission') use ($student, $buildItems) {

                // 1. Update challan totals
                $challanModel->total_amount = $total;
                $challanModel->concession_amount = $concession;
                $challanModel->save();

                // 2. Wipe old heads
                ChallanHead::where('challan_id', $challanModel->id)->delete();

                // 3. Wipe old journal items via existing voucher
                $voucher = JournalEntry::where('category', $category)
                    ->where('reference_id', $challanModel->id)
                    ->first();
                if ($voucher) {
                    JournalItem::where('journal', $voucher->id)->delete();
                    // We'll reuse the same JournalEntry — delete it so jrentry
                    // creates a fresh one with the correct sequence
                    $voucher->delete();
                }

                // 4. Re-insert challan heads and build items for jrentry
                $itemsForJr = [];
                foreach ($feeHeads as $feeHead) {
                    $isArray = is_array($feeHead);

                    $challanHead = new ChallanHead;
                    $challanHead->challan_id = $challanModel->id;
                    $challanHead->head_id = $isArray ? $feeHead['head_id'] : $feeHead->head_id;
                    $challanHead->price = $isArray ? $feeHead['price'] : $feeHead->price;
                    $challanHead->concession = $isArray ? $feeHead['concession'] : $feeHead->concession;
                    $challanHead->save();

                    $itemsForJr[] = [
                        'prod_id' => $challanHead->id,           // fresh ChallanHead id
                        'head' => $challanHead->head_id,
                        'price' => $challanHead->price,
                        'quantity' => 1,
                        'concession' => $challanHead->concession,
                        'total' => $challanHead->price - $challanHead->concession,
                    ];
                }

                // 5. Call jrentry — handles income, receivable, AND concession
                //    discount entries identically to bulkchallan
                $voucherId = Utility::jrentry([
                    'id' => $challanModel->id,
                    'no' => $challanModel->challanNo,
                    'date' => $challanModel->challan_date,
                    'reference' => $challanModel->student_id,
                    'category' => $category,
                    'user_id' => $challanModel->student_id,
                    'user_type' => 'Student',
                    'owned_by' => $challanModel->owned_by,
                    'created_by' => $challanModel->created_by,
                    'items' => $itemsForJr,
                ]);

                $challanModel->voucher_id = $voucherId;
                $challanModel->save();

                return $voucherId;
            };

            // ================================================================
            // CASE A: Multiple admission challans already exist for this student
            // ================================================================
            $checkChallans = Challans::with('heads')
                ->where('student_id', $challan->student_id)
                ->where('challan_type', 'Admission')
                ->get();

            if (count($checkChallans) > 1) {

                $allHeads = $checkChallans->flatMap(fn($c) => $c->heads)->toArray();

                for ($i = 0; $i < count($allHeads); $i++) {
                    if ($inst1Amounts[$i] == '100') {
                        $totalInst1 += $allHeads[$i]['price'];
                        $feeHeadsInst1[] = $allHeads[$i];
                        $concessionInst1 += $allHeads[$i]['concession'];
                    } elseif ($inst1Amounts[$i] == '0') {
                        $totalInst2 += $allHeads[$i]['price'];
                        $feeHeadsInst2[] = $allHeads[$i];
                        $concessionInst2 += $allHeads[$i]['concession'];
                    }
                }

                // Rebuild inst1 (existing challan)
                $rebuildChallan($existingChallan, $feeHeadsInst1, $totalInst1, $concessionInst1);

                // Find the second challan
                $secondChallan = Challans::where('id', '!=', $existingChallan->id)
                    ->where('student_id', $challan->student_id)
                    ->where('challan_type', 'Admission')
                    ->first();

                if (count($feeHeadsInst2) > 0) {
                    // Rebuild inst2 (second challan)
                    $rebuildChallan($secondChallan, $feeHeadsInst2, $totalInst2, $concessionInst2);
                } else {
                    // Nothing in inst2 — delete second challan + its journal
                    ChallanHead::where('challan_id', $secondChallan->id)->delete();
                    $voucher2 = JournalEntry::where('category', 'Admission')
                        ->where('reference_id', $secondChallan->id)->first();
                    if ($voucher2) {
                        JournalItem::where('journal', $voucher2->id)->delete();
                        $voucher2->delete();
                    }
                    $secondChallan->delete();
                }

            } else {
                // ================================================================
                // CASE B: Single admission challan — split into inst1 + inst2
                // ================================================================

                for ($i = 0; $i < count($feeHeadIds); $i++) {
                    $feeHead = ChallanHead::with('feeHead')
                        ->where('challan_id', $existingChallan->id)
                        ->where('head_id', $feeHeadIds[$i])
                        ->first();

                    if (!$feeHead)
                        continue;

                    if ($inst1Amounts[$i] == '100') {
                        $totalInst1 += $feeHead->price;
                        $feeHeadsInst1[] = $feeHead;
                        $concessionInst1 += $feeHead->concession;
                    } elseif ($inst1Amounts[$i] == '0') {
                        $totalInst2 += $feeHead->price;
                        $feeHeadsInst2[] = $feeHead;
                        $concessionInst2 += $feeHead->concession;
                    }
                }

                // Rebuild existing challan with inst1 heads
                $rebuildChallan($existingChallan, $feeHeadsInst1, $totalInst1, $concessionInst1);

                // Create brand-new challan for inst2 if needed
                if (count($feeHeadsInst2) > 0) {
                    $nextMonth = $challanDate->copy()->addMonth();

                    $newChallan = new Challans;
                    $newChallan->student_id = $student->id;
                    $newChallan->rollno = $student->roll_no;
                    $newChallan->class_id = $student->class_id;
                    $newChallan->challanNo = $this->challanNo();
                    $newChallan->challan_date = date('Y-m-d');
                    $newChallan->challan_type = 'Admission';
                    $newChallan->fee_month = $nextMonth->startOfMonth()->toDateString();
                    $newChallan->total_amount = $totalInst2;
                    $newChallan->issue_date = $issueDate;
                    $newChallan->due_date = $nextMonth->copy()->addWeek()->toDateString();
                    $newChallan->status = 'Issued';
                    $newChallan->owned_by = $existingChallan->owned_by;
                    $newChallan->created_by = $existingChallan->created_by;
                    $newChallan->session_id = $existingChallan->session_id;
                    $newChallan->concession_amount = $concessionInst2;
                    $newChallan->save();

                    // Insert heads + call jrentry for the new challan
                    $itemsForJr = [];
                    foreach ($feeHeadsInst2 as $feeHead) {
                        $challanHead = new ChallanHead;
                        $challanHead->challan_id = $newChallan->id;
                        $challanHead->head_id = $feeHead->head_id;
                        $challanHead->price = $feeHead->price;
                        $challanHead->concession = $feeHead->concession;
                        $challanHead->save();

                        $itemsForJr[] = [
                            'prod_id' => $challanHead->id,
                            'head' => $challanHead->head_id,
                            'price' => $challanHead->price,
                            'quantity' => 1,
                            'concession' => $challanHead->concession,
                            'total' => $challanHead->price - $challanHead->concession,
                        ];
                    }

                    $dataret = Utility::jrentry([
                        'id' => $newChallan->id,
                        'no' => $newChallan->challanNo,
                        'date' => $newChallan->challan_date,
                        'reference' => $newChallan->student_id,
                        'category' => 'Admission',
                        'user_id' => $newChallan->student_id,
                        'user_type' => 'Student',
                        'owned_by' => $newChallan->owned_by,
                        'created_by' => $newChallan->created_by,
                        'items' => $itemsForJr,
                    ]);
                    $newChallan->voucher_id = $dataret;
                    $newChallan->save();
                }
            }

            DB::commit();
            return redirect()->route('admissionchallanlist')
                ->with('success', 'Installment challans have been created successfully');

        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $challan = Challans::with(
            'heads',
            'enrollstudent',
            'enrollstudent.StudentRegistration'
        )->findOrFail($id);

        $student = StudentRegistration::with('class', 'session', 'branches')
            ->findOrFail($challan->student_id);

        $student_fee_heads = FeeHead::where('created_by', auth()->user()->creatorId())->get();

        $student_fee_structure = StudentFeeStructure::where('reg_id', $student->id)->get();

        $challanHeadIds = $challan->heads->pluck('head_id')->toArray();

        $classfee = StudentFeeStructure::with('feehead')
            ->where('reg_id', $student->id)
            ->where('owned_by', $student->owned_by)
            ->where(function ($query) use ($challanHeadIds) {
                $query->where('checked_status', 1)
                    ->orWhereIn('head_id', $challanHeadIds);
            })
            ->get();

        if ($classfee->isEmpty()) {

            $fee_heads = ClassWiseFee::where('session_id', $student->session_id)
                ->where('class_id', $student->class_id)
                ->where('owned_by', $student->owned_by)
                ->get();

            foreach ($fee_heads as $head) {
                StudentFeeStructure::updateOrCreate(
                    [
                        'reg_id'    => $student->id,
                        'branch_id' => $student->owned_by,
                        'head_id'   => $head->head_id,
                    ],
                    [
                        'amount'       => $head->amount,
                        'class_id'     => $student->class_id,
                        'discount'     => 0,
                        'owned_by'     => $student->owned_by,
                        'created_by'   => $student->created_by,
                        'checked_status'=> 1,
                    ]
                );
            }

            // 🔁 Re-fetch after creation
            $classfee = StudentFeeStructure::with('feehead')
                ->where('reg_id', $student->id)
                ->where('owned_by', $student->owned_by)
                ->where(function ($query) use ($challanHeadIds) {
                    $query->where('checked_status', 1)
                        ->orWhereIn('head_id', $challanHeadIds);
                })
                ->get();
        }

        $classfee = $classfee->unique('head_id')->values();

        $concession = Concession::with('concession')
            ->where('student_id', $challan->student_id)
            ->where('active_status', '!=', 0)
            ->where('status', 'Approved')
            ->whereDate('end_date', '>=', now())
            ->orderByDesc('id')
            ->first();

        if (! $concession) {
            $concession = Concession::with('concession')
                ->where('student_id', $challan->student_id)
                ->where('active_status', '!=', 0)
                ->where('status', 'Approved')
                ->whereNull('end_date')
                ->orderByDesc('id')
                ->first();
        }

        return view(
            'challans.nchallanedit',
            compact(
                'challan',
                'student_fee_heads',
                'student_fee_structure',
                'student',
                'classfee',
                'concession'
            )
        );
    }


   public function update(Request $request, $id)
{
    DB::beginTransaction();
    try {
        // Fetch the challan
        $challan = Challans::findOrFail($id);

            if ($challan->paid > 0 || strtolower($challan->status) == 'paid') {
                return redirect()->back()->with('error', 'Cannot edit a challan that has already been paid.');
            }

            $challan->issue_date = $request->issue_date;
            $challan->due_date = $request->due_date;
            $challan->remarks = $request->remarks;

            $fee_month = date('Y-m-d', strtotime($request->fee_month));
            $targetDate = $fee_month;

            $tuitionFeeHead = FeeHead::where('fee_head', 'LIKE', '%Tuition%')->first();

            // Does current challan have tuition?
            $hasTuition = $challan->heads()
                ->where('head_id', $tuitionFeeHead->id)
                ->exists();

            // Base query (same student, admission, same month, exclude current)
            $baseQuery = Challans::where('student_id', $challan->student_id)
                ->where('challan_type', 'Admission')
                ->where('id', '!=', $challan->id)
                ->where(function ($q) use ($targetDate) {
                    $q->whereRaw("STR_TO_DATE(fee_month, '%Y-%m-%d') = ?", [$targetDate])
                        ->orWhereRaw("FIND_IN_SET(?, other_months)", [$targetDate]);
                });

            if ($hasTuition) {
                // 🔥 FIX: block if ANY challan exists (not just tuition)
                if ((clone $baseQuery)->exists()) {
                    return redirect()->back()->with(
                        'error',
                        'Admission challan for this month already exists (cannot duplicate with tuition).'
                    );
                }

            } else {
                // Non-tuition installment
                if ((clone $baseQuery)->exists()) {
                    return redirect()->back()->with(
                        'error',
                        'Admission challan for this month already exists.'
                    );
                }
            }
			if($request->fee_month && $request->fee_month != null){
				$challan->fee_month = $fee_month;
			}            

        // Handle Fee Subscription
        $subscriptionMap = [
            'monthly' => 1,
            'bi-monthly' => 2,
            'quarterly' => 3,
            '4-monthly' => 4,
            '5-monthly' => 5,
            '6-monthly' => 6,
            '7-monthly' => 7,
            '8-monthly' => 8,
            '9-monthly' => 9,
            '10-monthly' => 10,
            '11-monthly' => 11,
            'yearly' => 12,
        ];

        $duration = $subscriptionMap[$request->fee_subscription] ?? 1;

        if ($duration > 1) {
            // Determine start date (prefer fee_month if available, else issue_date)
            $startDate = $challan->fee_month ? \Carbon\Carbon::parse($challan->fee_month) : \Carbon\Carbon::parse($challan->issue_date);

            $dates = [];
            for ($i = 0; $i < $duration; $i++) {
                $dates[] = $startDate->copy()->addMonths($i)->format('Y-m-d');
            }
            $challan->other_months = implode(',', $dates);
        } else {
            $challan->other_months = null;
        }

        $challan->save();

        // Get selected heads and their corresponding amounts
        $checkedHeads = $request->input('checked', []);
        $amounts = $request->input('amount', []);
        $actual_amounts = $request->input('actual_amount', []);
        $total_new_amount = 0;

        $challan_amounts = $request->input('challan_amount', []);

        // Get heads that need to be removed
        $challanHeadsextra = ChallanHead::where('challan_id', $challan->id)->whereNotIn('head_id', $checkedHeads)->get();
        
        if (count($challanHeadsextra) > 0) {
            foreach ($challanHeadsextra as $head) {
                JournalItem::where('entry_id', $head->id)
                    ->where('types', 'Challan')
                    ->delete();
                $head->delete();
            }
        }
        
        // Check if no heads are checked (all heads removed)
        if (empty($checkedHeads) || count($checkedHeads) == 0) {
            // Mark challan as paid since there are no heads left
            $challan->status = 'paid';
            $challan->total_amount = 0;
            $challan->paid_amount = 0;
            $challan->save();
            
            DB::commit();
            
            $route = match ($challan->challan_type) {
                'Admission' => 'admissionchallanlist',
                'ReAdmission' => 'readmissionchallanlist',
                'Registration' => 'registrationchallanlist',
                default => 'regularchallanlist',
            };

            return redirect()->route($route)->with('success', 'Challan has been marked as paid (all heads removed)');
        }
        
        foreach ($checkedHeads as $headId) {

            $unit_base = isset($amounts[$headId]) ? (float) $amounts[$headId] : 0;
            $unit_net = isset($actual_amounts[$headId]) ? (float) $actual_amounts[$headId] : 0;
            $final_payable = isset($challan_amounts[$headId]) ? (float) $challan_amounts[$headId] : 0;

            $unit_concession = $unit_base - $unit_net;

            // Calculate Gross Price and Total Concession based on the multiplier
            if ($unit_net > 0) {
                $multiplier = $final_payable / $unit_net;
                $gross_price = round($unit_base * $multiplier);
                $total_concession = round($unit_concession * $multiplier);

                // Small adjustment to ensure Price - Concession == Final Payable exactly
                if (($gross_price - $total_concession) != $final_payable) {
                    $total_concession = $gross_price - $final_payable;
                }

            } else {
                $gross_price = $final_payable;
                $total_concession = 0;
            }

            $challanHead = ChallanHead::where('challan_id', $challan->id)->where('head_id', $headId)->first();

            if ($challanHead) {

                if ($final_payable != ($challanHead->price - $challanHead->concession)) {
                    $challanHead->price = $gross_price;
                    $challanHead->concession = $total_concession;
                }
                $challanHead->save();

                $journalEntry = JournalEntry::where('reference_id', $challan->id)
                    ->where('voucher_type', 'JV')
                    ->first();
                    
                if ($journalEntry) {
                    // Update regular journal items (non-discount)
                    $journalItems = JournalItem::where('entry_id', $challanHead->id)
                        ->where('types', 'Challan')
                        ->where('is_discount', 0)
                        ->get();
                        
                    if(!empty($journalItems) && count($journalItems) > 0) {
                        foreach ($journalItems as $item) {
                            // Update amounts
                            $item->user_id = $challan->student_id;
                            $item->user_type = 'Student';
                            $item->branch_id = $challan->owned_by;
                            if ($item->credit != 0) {
                                $item->credit = $gross_price;
                                $item->description = 'Income Account: Roll no '.@$challan->student->roll_no.' Challan no '.@$challan->challanNo.' - '.@$challan->student->stdname.' - '.@$challan->fee_month.' - '.@$challan->student->branch->name;
                            } else {
                                if (strpos($item->description, 'Account Receivable:') !== false || strpos($item->description, 'Reciveable') !== false) {
                                    $item->debit = $gross_price;
                                    $item->description = 'Account Receivable: Roll no '.@$challan->student->roll_no.' Challan no '.@$challan->challanNo.' - '.@$challan->student->stdname.' - '.@$challan->fee_month.' - '.@$challan->student->branch->name;
                                }
                            }
                            $item->save();
                        }
                    } else {
                        // Journal items not found - create them
                        $feeHead = FeeHead::find($headId);
                        
                        if ($feeHead) {
                            // Income Account Entry
                            $journalItem = new JournalItem;
                            $journalItem->entry_id = $challanHead->id;
                            $journalItem->types = 'Challan';
                            $journalItem->journal = $journalEntry->id;
                            $journalItem->head = $headId;
                            $journalItem->description = 'Income Account: Roll no '.@$challan->student->roll_no.' Challan no '.@$challan->challanNo.' - '.@$challan->student->stdname.' - '.@$challan->fee_month.' - '.@$challan->student->branch->name;
                            $journalItem->credit = $gross_price;
                            $journalItem->debit = 0;
                            $journalItem->user_type = 'Student';
                            $journalItem->user_id = $challan->student->id;
                            $journalItem->account = $feeHead->account_id;
                            $journalItem->is_discount = 0;
                            $journalItem->save();
                            
                            // Receivable Entry
                            $account_recive = ChartOfAccount::where('id', $feeHead->receivable_account_id)->first();
                            $journalItem = new JournalItem;
                            $journalItem->journal = $journalEntry->id;
                            $journalItem->account = @$account_recive->id;
                            $journalItem->head = $headId;
                            $journalItem->description = 'Account Receivable: Roll no '.@$challan->student->roll_no.' Challan no '.@$challan->challanNo.' - '.@$challan->student->stdname.' - '.@$challan->fee_month.' - '.@$challan->student->branch->name;
                            $journalItem->entry_id = @$challanHead->id;
                            $journalItem->types = 'Challan';
                            $journalItem->user_type = 'Student';
                            $journalItem->user_id = $challan->student->id;
                            $journalItem->credit = 0;
                            $journalItem->debit = $gross_price;
                            $journalItem->is_discount = 0;
                            $journalItem->save();
                        }
                    }
                    
                    // Handle discount entries
                    $journalItemsDisc = JournalItem::where('entry_id', $challanHead->id)
                        ->where('types', 'Challan')
                        ->where('is_discount', 1)
                        ->get();
                        
                    if ($total_concession > 0 && count($journalItemsDisc) == 0) {
                        // Create new discount entries if they don't exist
                        $feeHead = FeeHead::find($headId);
                        $account_name = ChartOfAccount::where('id', $feeHead->discount_account_id)->first();
                        
                        // Discount Allowed Income
                        $journalItem = new JournalItem;
                        $journalItem->journal = $journalEntry->id;
                        $journalItem->account = $account_name->id;
                        $journalItem->head = $headId;
                        $journalItem->description = 'Discount Allowed Income: Roll no '.@$challan->student->roll_no.' Challan no '.@$challan->challanNo.' - '.@$challan->student->stdname.' - '.@$challan->fee_month.' - '.@$challan->student->branch->name;
                        $journalItem->user_id = $challan->student->id;
                        $journalItem->user_type = 'Student';
                        $journalItem->entry_id = $challanHead->id;
                        $journalItem->types = 'Challan';
                        $journalItem->is_discount = 1;
                        $journalItem->credit = 0;
                        $journalItem->debit = $total_concession;
                        $journalItem->save();
                        
                        // Discount Allowed Receivable
                        $account_recive = ChartOfAccount::where('id', $feeHead->receivable_account_id)->first();
                        $journalItem = new JournalItem;
                        $journalItem->journal = $journalEntry->id;
                        $journalItem->account = $account_recive->id;
                        $journalItem->head = $headId;
                        $journalItem->description = 'Discount Allowed Receivable: Roll no '.@$challan->student->roll_no.' Challan no '.@$challan->challanNo.' - '.@$challan->student->stdname.' - '.@$challan->fee_month.' - '.@$challan->student->branch->name;
                        $journalItem->user_id = $challan->student->id;
                        $journalItem->user_type = 'Student';
                        $journalItem->entry_id = $challanHead->id;
                        $journalItem->types = 'Challan';
                        $journalItem->is_discount = 1;
                        $journalItem->credit = $total_concession;
                        $journalItem->debit = 0;
                        $journalItem->save(); 
                    } elseif ($total_concession > 0 && count($journalItemsDisc) > 0) {
                        // Update existing discount entries
                        foreach ($journalItemsDisc as $item) {
                            // Update amounts
                            if ($item->credit != 0) {
                                $item->credit = $total_concession;
                            } else {
                                $item->debit = $total_concession;
                            }
                            
                            // Update descriptions
                            if (strpos($item->description, 'Discount Allowed Income:') !== false) {
                                $item->description = 'Discount Allowed Income: Roll no '.@$challan->student->roll_no.' Challan no '.@$challan->challanNo.' - '.@$challan->student->stdname.' - '.@$challan->fee_month.' - '.@$challan->student->branch->name;
                            } elseif (strpos($item->description, 'Discount Allowed Receivable:') !== false) {
                                $item->description = 'Discount Allowed Receivable: Roll no '.@$challan->student->roll_no.' Challan no '.@$challan->challanNo.' - '.@$challan->student->stdname.' - '.@$challan->fee_month.' - '.@$challan->student->branch->name;
                            }
                            
                            $item->save();
                        }
                    } elseif ($total_concession == 0 && count($journalItemsDisc) > 0) {
                        // Delete discount entries if concession is now 0
                        foreach ($journalItemsDisc as $item) {
                            $item->delete();
                        }
                    }
                }
            } else {
                // Create new ChallanHead
                $challanHead = new ChallanHead;
                $challanHead->challan_id = $challan->id;
                $challanHead->head_id = $headId;
                $challanHead->price = $gross_price;
                $challanHead->paid = 0;
                $challanHead->concession = $total_concession;
                $challanHead->save();
                
                $journalEntry = JournalEntry::where('reference_id', $challan->id)
                    ->where('voucher_type', 'JV')
                    ->first();
                    
                $feeHead = FeeHead::find($headId);
                
                if ($journalEntry && $feeHead) {
                    // Income Account Entry
                    $journalItem = new JournalItem;
                    $journalItem->entry_id = $challanHead->id;
                    $journalItem->types = 'Challan';
                    $journalItem->journal = $journalEntry->id;
                    $journalItem->head = $headId;
                    $journalItem->description = 'Income Account: Roll no '.@$challan->student->roll_no.' Challan no '.@$challan->challanNo.' - '.@$challan->student->stdname.' - '.@$challan->fee_month.' - '.@$challan->student->branch->name;
                    $journalItem->credit = $gross_price;
                    $journalItem->debit = 0;
                    $journalItem->user_type = 'Student';
                    $journalItem->user_id = $challan->student->id;
                    $journalItem->account = $feeHead->account_id;
                    $journalItem->save();
                    
                    // Receivable Entry
                    $account_recive = ChartOfAccount::where('id', $feeHead->receivable_account_id)->first();
                    $journalItem = new JournalItem;
                    $journalItem->journal = $journalEntry->id;
                    $journalItem->account = @$account_recive->id;
                    $journalItem->head = $headId;
                    $journalItem->description = 'Account Receivable: Roll no '.@$challan->student->roll_no.' Challan no '.@$challan->challanNo.' - '.@$challan->student->stdname.' - '.@$challan->fee_month.' - '.@$challan->student->branch->name;
                    $journalItem->entry_id = @$challanHead->id;
                    $journalItem->types = 'Challan';
                    $journalItem->user_type = 'Student';
                    $journalItem->user_id = $challan->student->id;
                    $journalItem->credit = 0;
                    $journalItem->debit = $gross_price;
                    $journalItem->save();
                    
                    // Discount Entries (if applicable)
                    if ($total_concession > 0) {
                        $account_name = ChartOfAccount::where('id', $feeHead->discount_account_id)->first();
                        
                        // Discount Allowed Income
                        $journalItem = new JournalItem;
                        $journalItem->journal = $journalEntry->id;
                        $journalItem->account = $account_name->id;
                        $journalItem->head = $headId;
                        $journalItem->description = 'Discount Allowed Income: Roll no '.@$challan->student->roll_no.' Challan no '.@$challan->challanNo.' - '.@$challan->student->stdname.' - '.@$challan->fee_month.' - '.@$challan->student->branch->name;
                        $journalItem->user_id = @$challan->student->id;
                        $journalItem->user_type = 'Student';
                        $journalItem->entry_id = $challanHead->id;
                        $journalItem->types = 'Challan';
                        $journalItem->is_discount = 1;
                        $journalItem->credit = 0;
                        $journalItem->debit = $total_concession;
                        $journalItem->save();
                        
                        // Discount Allowed Receivable
                        $account_recive = ChartOfAccount::where('id', $feeHead->receivable_account_id)->first();
                        $journalItem = new JournalItem;
                        $journalItem->journal = $journalEntry->id;
                        $journalItem->account = $account_recive->id;
                        $journalItem->head = $headId;
                        $journalItem->description = 'Discount Allowed Receivable: Roll no '.@$challan->student->roll_no.' Challan no '.@$challan->challanNo.' - '.@$challan->student->stdname.' - '.@$challan->fee_month.' - '.@$challan->student->branch->name;
                        $journalItem->user_id = $challan->student->id;
                        $journalItem->user_type = 'Student';
                        $journalItem->entry_id = $challanHead->id;
                        $journalItem->types = 'Challan';
                        $journalItem->is_discount = 1;
                        $journalItem->credit = $total_concession;
                        $journalItem->debit = 0;
                        $journalItem->save();
                    }
                }
            }
            $total_new_amount += $final_payable;
        }
        
        // Update challan total amount
        $challan->total_amount = $total_new_amount + $challan->concession_amount;
        
        // Recalculate paid_amount from all challan heads
        $totalPaid = ChallanHead::where('challan_id', $challan->id)->sum('paid');
        $challan->paid_amount = $totalPaid;
        
        if ($challan->paid_amount >= ($challan->total_amount - $challan->concession_amount)) {
            $challan->status = 'paid';
        }
        
        $challan->save();
        
        DB::commit();
        
        // Redirect based on challan type
        $route = match ($challan->challan_type) {
            'Admission' => 'admissionchallanlist',
            'ReAdmission' => 'readmissionchallanlist',
            'Registration' => 'registrationchallanlist',
            default => 'regularchallanlist',
        };

        return redirect()->route($route)->with('success', 'Challan has been updated successfully');

    } catch (\Exception $e) {
        DB::rollback();
        dd($e);

        return redirect()->back()->with('error', $e->getMessage());
    }
}

    public function challanLateFine(Request $request)
    {
        DB::beginTransaction();
        try {
            $challans = Challans::where('due_date', '<', date('Y-m-d'))
                ->whereMonth('due_date', 5)
                ->whereYear('due_date', date('Y'))
                ->where('challan_type', 'Regular')
                ->where('status', '!=', 'Paid')->get();
            // dd($challans);
            // late surcharge head
            $lateSurchargeHead = FeeHead::where('fee_head', 'LIKE', '%LATE FEE%')->first();
            $fine_amount = 150;
            if (! $lateSurchargeHead) {
                throw new \Exception('Late Surcharge Fee Head not found');
            }
            foreach ($challans as $challan) {
                $challanHead = ChallanHead::where('head_id', $lateSurchargeHead->id)
                    ->where('challan_id', $challan->id)->first();

                if ($challanHead) {
                    if ($challanHead->price < 1500) {
                        $challanHead->price += $fine_amount;
                        $challanHead->save();
                        $challan->total_amount = $challan->total_amount + $fine_amount;
                        $challan->save();
                        $journalItem = JournalItem::where('entry_id', $challanHead->id)
                            ->where('journal', $challan->voucher_id)
                            ->where('types', 'Challan')
                            ->get();
                        if ($journalItem) {
                            foreach ($journalItem as $item) {
                                if ($item->credit != 0) {
                                    $item->credit += $fine_amount;
                                    $item->save();
                                } else {
                                    $item->debit += $fine_amount;
                                    $item->save();
                                }
                            }
                        }
                    } else {
                        continue;
                    }
                } else {
                    $challanHead = new ChallanHead;
                    $challanHead->challan_id = $challan->id;
                    $challanHead->head_id = $lateSurchargeHead->id;
                    $challanHead->price = $fine_amount;
                    $challanHead->concession = 0;
                    $challanHead->save();
                    $challan->total_amount = $challan->total_amount + $fine_amount;
                    $challan->save();
                    $journalEntry = JournalEntry::where('id', $challan->voucher_id)
                        ->where('voucher_type', 'JV')
                        ->first();
                    if ($journalEntry) {
                        $account_name = ChartOfAccount::where('id', $lateSurchargeHead->account_id)->first();
                        $journalItem = new JournalItem;
                        $journalItem->journal = $journalEntry->id;
                        $journalItem->account = @$account_name->id;
                        $journalItem->head = $lateSurchargeHead->id;
                        $journalItem->description = @$account_name->name.' for Challan no : '.@$challan->challanNo.' and student name : '.@$challan->student->stdname;
                        $journalItem->entry_id = @$challanHead->id;
                        $journalItem->types = 'Challan';
                        $journalItem->credit = $fine_amount;
                        $journalItem->debit = 0;
                        $journalItem->save();

                        //  reciveable entry
                        $account_recive = ChartOfAccount::where('id', $lateSurchargeHead->receivable_account_id)->first();
                        $journalItem = new JournalItem;
                        $journalItem->journal = $journalEntry->id;
                        $journalItem->account = @$account_recive->id;
                        $journalItem->head = $lateSurchargeHead->id;
                        $journalItem->description = 'Reciveable of Challan id : '.$challan->challanNo;
                        $journalItem->entry_id = @$challanHead->id;
                        $journalItem->types = 'Challan';
                        $journalItem->credit = 0;
                        $journalItem->debit = $fine_amount;
                        $journalItem->save();
                    }
                }
            }
            DB::commit();

            return redirect()->back()->with('success', 'Challan Late Fee has been updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function withdrawchallanlist(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('created_by', \Auth::user()->creatorId())->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $students->prepend('All Students', 'all');
            $query = challans::orderBy('id', 'Desc');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('owned_by', \Auth::user()->ownedId())->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $students->prepend('All Students', 'all');
            $query = challans::orderBy('id', 'Desc');
        }
        $session = Session::where('created_by', \Auth::user()->creatorId())->get()->pluck('year', 'id');
        $session->prepend('Select Session', '');

        if (! empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            // $session = Session::where('owned_by', '=', $request->branches)->get()->pluck('year', 'id');
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            $students = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('branch', '=', $request->branches)->where('student_status', '!=', 'Registered')->get()->pluck('stdname', 'roll_no');
            $students->prepend('All Students', 'all');
            // $session->prepend('Select Session', '');
            $class->prepend('Select Class', '');
        }
        if (! empty($request->session)) {
            $query->where('session_id', '=', $request->session);
        }
        if (! empty($request->class)) {
            $query->where('class_id', '=', $request->class);
        }
        if (! empty($request->student) && $request->student != 'all') {
            $query->where('rollno', '=', $request->student);
        }
        if (! empty($request->challan_date)) {
            $query->whereYear('fee_month', date('Y', strtotime($request->challan_date)))->whereMonth('fee_month', date('m', strtotime($request->challan_date)));
        }
        $class = [];
        $pattern = '%withdrawal%';
        $breadcrumb = 'Withdrawal Challans';
        $challan_list = $query->whereRaw('LOWER(challan_type) LIKE ?', [strtolower($pattern)])->paginate(25);
        $type = strtolower('Withdrawal');

        return view('students.challanlists.adm_challan_list', compact('branches', 'students', 'session', 'class', 'challan_list', 'breadcrumb', 'type'));
    }

    public function installment_challan_readmission(Request $request, $id)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            $existingChallan = Challans::with('heads', 'heads.feeHead')->findOrFail($id);
            $issueDate = Carbon::now()->toDateString();
            $dueDate = Carbon::now()->addWeek()->toDateString();
            $challanDate = Carbon::parse($existingChallan->challan_date);
            $year = $challanDate->year;
            $month = $challanDate->month;
            $feeHeadIds = $request->input('fee_head_id');
            $inst1Amounts = $request->input('head_amount_inst1');
            $inst2Amounts = $request->input('head_amount_inst2');
            $student = StudentRegistration::findOrFail($existingChallan->student_id);
            $challan = $existingChallan;
            $heads = $feeHeadIds;
            if ($request->type == 'view') {
                return view('challans.installchallan', compact('challan', 'heads', 'inst1Amounts', 'inst2Amounts', 'feeHeadIds'));
            }
            $totalInst1 = 0;
            $totalInst2 = 0;
            $feeHeadsInst1 = [];
            $feeHeadsInst2 = [];
            $concessionInst1 = 0;
            $concessionInst2 = 0;

            $checkChallans = Challans::with('heads')->where('student_id', $challan->student_id)
                ->where('challan_type', 'Readmission')
                ->get();
            if (count($checkChallans) > 1) {
                $allHeads = $checkChallans->flatMap(function ($challan) {
                    return $challan->heads;
                })->toArray();
                for ($i = 0; $i < count($allHeads); $i++) {
                    if ($inst1Amounts[$i] == '100') {
                        $totalInst1 += $allHeads[$i]['price'];
                        $feeHeadsInst1[] = $allHeads[$i];
                        $concessionInst1 += $allHeads[$i]['concession'];
                    } elseif ($inst1Amounts[$i] == '0') {
                        $totalInst2 += $allHeads[$i]['price'];
                        $feeHeadsInst2[] = $allHeads[$i];
                        $concessionInst2 += $allHeads[$i]['concession'];
                    }
                }
                $existingChallan->total_amount = $totalInst1;
                $existingChallan->concession_amount = $concessionInst1;
                $existingChallan->save();

                ChallanHead::where('challan_id', $existingChallan->id)->delete();
                $voucher = JournalEntry::where('category', 'Readmission')->where('reference_id', $existingChallan->id)->first();
                $journalItem = JournalItem::where('journal', $voucher->id)->delete();
                foreach ($feeHeadsInst1 as $feeHead) {
                    $challanHead = new ChallanHead;
                    $challanHead->challan_id = $existingChallan['id'];
                    $challanHead->head_id = $feeHead['head_id'];
                    $challanHead->price = $feeHead['price'];
                    $challanHead->concession = $feeHead['concession'];
                    $challanHead->save();
                    $feeHead['pord_id'] = $challanHead->id;

                    $head_id = FeeHead::where('id', $feeHead['head_id'])->first();
                    $account_name = ChartOfAccount::where('id', $head_id->account_id)->first();
                    $journalItem = new JournalItem;
                    $journalItem->journal = $voucher->id;
                    $journalItem->account = @$account_name->id;
                    $journalItem->head = $feeHead['head_id'];
                    $journalItem->description = $account_name->name;
                    $journalItem->entry_id = @$challanHead->id;
                    $journalItem->types = 'Challan';
                    $journalItem->credit = $feeHead['price'] - $feeHead['concession'];
                    $journalItem->debit = 0;
                    $journalItem->save();

                    //  reciveable entry
                    $account_recive = ChartOfAccount::where('id', $head_id->receivable_account_id)->first();
                    $journalItem = new JournalItem;
                    $journalItem->journal = $voucher->id;
                    $journalItem->account = @$account_recive->id;
                    $journalItem->head = $feeHead['head_id'];
                    $journalItem->description = 'Reciveable of Challan no : '.@$existingChallan->challanNo;
                    $journalItem->entry_id = @$challanHead->id;
                    $journalItem->types = 'Challan';
                    $journalItem->credit = 0;
                    $journalItem->debit = $feeHead['price'] - $feeHead['concession'];
                    $journalItem->save();
                }

                $secondchallan = Challans::where('id', '!=', $existingChallan->id)->where('student_id', $challan->student_id)->where('challan_type', 'Admission')->first();
                if (count($feeHeadsInst2) > 0) {
                    ChallanHead::where('challan_id', $secondchallan->id)->delete();
                    $voucher = JournalEntry::where('category', 'Readmission')->where('reference_id', $secondchallan->id)->first();
                    $journalItem = JournalItem::where('journal', $voucher->id)->delete();
                    foreach ($feeHeadsInst2 as $feeHead) {
                        $challanHead = new ChallanHead;
                        $challanHead->challan_id = $secondchallan['id'];
                        $challanHead->head_id = $feeHead['head_id'];
                        $challanHead->price = $feeHead['price'];
                        $challanHead->concession = $feeHead['concession'];
                        $challanHead->save();

                        $head_id = FeeHead::where('id', $feeHead->head_id)->first();
                        $account_name = ChartOfAccount::where('id', $head_id->account_id)->first();
                        $journalItem = new JournalItem;
                        $journalItem->journal = $voucher->id;
                        $journalItem->account = @$account_name->id;
                        $journalItem->head = $feeHead->head_id;
                        $journalItem->description = $account_name->name;
                        $journalItem->entry_id = @$challanHead->id;
                        $journalItem->types = 'Challan';
                        $journalItem->credit = $feeHead->price - $feeHead->concession;
                        $journalItem->debit = 0;
                        $journalItem->save();

                        //  reciveable entry
                        $account_recive = ChartOfAccount::where('id', $head_id->receivable_account_id)->first();
                        $journalItem = new JournalItem;
                        $journalItem->journal = $voucher->id;
                        $journalItem->account = @$account_recive->id;
                        $journalItem->head = $feeHead->head_id;
                        $journalItem->description = 'Reciveable of Challan no : '.@$secondchallan->challanNo;
                        $journalItem->entry_id = @$challanHead->id;
                        $journalItem->types = 'Challan';
                        $journalItem->credit = 0;
                        $journalItem->debit = $feeHead->price - $feeHead->concession;
                        $journalItem->save();
                    }
                    $secondchallan->total_amount = $totalInst2;
                    $secondchallan->concession_amount = $concessionInst2;
                    $secondchallan->save();
                } else {
                    ChallanHead::where('challan_id', $secondchallan->id)->delete();
                    $secondchallan->delete();
                    $voucher = JournalEntry::where('category', 'Readmission')->where('reference_id', $secondchallan->id)->first();
                    $journalItem = JournalItem::where('journal', $voucher->id)->delete();
                    $voucher->delete();
                }
            } else {

                for ($i = 0; $i < count($feeHeadIds); $i++) {
                    // dd($feeHeadIds);
                    $feeHead = ChallanHead::with('feeHead')->where('challan_id', $existingChallan->id)->where('head_id', $feeHeadIds[$i])->first();

                    if ($feeHead) {
                        if ($inst1Amounts[$i] == '100') {
                            $totalInst1 += $feeHead->price;
                            $feeHeadsInst1[] = $feeHead;
                            $concessionInst1 += $feeHead->concession;
                        } elseif ($inst1Amounts[$i] == '0') {
                            $totalInst2 += $feeHead->price;
                            $feeHeadsInst2[] = $feeHead;
                            $concessionInst2 += $feeHead->concession;
                        }
                    }
                }
                // dd($totalInst1,$concessionInst1);
                $existingChallan->total_amount = $totalInst1;
                $existingChallan->concession_amount = $concessionInst1;
                $existingChallan->save();
                ChallanHead::where('challan_id', $existingChallan->id)->delete();
                $voucher = JournalEntry::where('category', 'Readmission')->where('reference_id', $existingChallan->id)->first();
                $journalItem = JournalItem::where('journal', $voucher->id)->delete();
                foreach ($feeHeadsInst1 as $feeHead) {
                    $challanHead = new ChallanHead;
                    $challanHead->challan_id = $existingChallan->id;
                    $challanHead->head_id = $feeHead->head_id;
                    $challanHead->price = $feeHead->price;
                    $challanHead->concession = $feeHead->concession;
                    $challanHead->save();
                    $feeHead['pord_id'] = $challanHead->id;

                    $head_id = FeeHead::where('id', $feeHead->head_id)->first();
                    $account_name = ChartOfAccount::where('id', $head_id->account_id)->first();
                    $journalItem = new JournalItem;
                    $journalItem->journal = $voucher->id;
                    $journalItem->account = @$account_name->id;
                    $journalItem->head = $feeHead->head_id;
                    $journalItem->description = $account_name->name;
                    $journalItem->entry_id = @$challanHead->id;
                    $journalItem->types = 'Challan';
                    $journalItem->credit = $feeHead->price - $feeHead->concession;
                    $journalItem->debit = 0;
                    $journalItem->save();

                    //  reciveable entry
                    $account_recive = ChartOfAccount::where('id', $head_id->receivable_account_id)->first();
                    $journalItem = new JournalItem;
                    $journalItem->journal = $voucher->id;
                    $journalItem->account = @$account_recive->id;
                    $journalItem->head = $feeHead->head_id;
                    $journalItem->description = 'Reciveable of Challan no : '.@$existingChallan->challanNo;
                    $journalItem->entry_id = @$challanHead->id;
                    $journalItem->types = 'Challan';
                    $journalItem->credit = 0;
                    $journalItem->debit = $feeHead->price - $feeHead->concession;
                    $journalItem->save();
                }
                // $dataInst1 = [
                //     'id' => $existingChallan->id,
                //     'no' => $existingChallan->challanNo,
                //     'date' => $existingChallan->challan_date,
                //     'reference' => $existingChallan->student_id,
                //     'category' => 'Admission',
                //     'user_id' => $existingChallan->student_id,
                //     'user_type' => 'Student',
                //     'owned_by' => $existingChallan->owned_by,
                //     'created_by' => $existingChallan->created_by,
                //     'items' => [],
                // ];
                // foreach ($feeHeadsInst1 as $feeHead) {
                //     $dataInst1['items'][] = [
                //         'prod_id' => $feeHead->pord_id,
                //         'head' => $feeHead->head_id,
                //         'price' => $feeHead->amount,
                //         'quantity' => 1,
                //         'concession' => $challanHead->concession,
                //         'total' => $feeHead->amount - $challanHead->concession,
                //     ];
                // }
                // Utility::jrentry($dataInst1);
                if (count($feeHeadsInst2) > 0) {
                    $nextMonth = $challanDate->copy()->addMonth();
                    $newChallan = new Challans;
                    $newChallan->student_id = $student->id;
                    $newChallan->rollno = $student->roll_no;
                    $newChallan->class_id = $student->class_id;
                    $newChallan->challanNo = $this->challanNo();
                    $newChallan->challan_date = date('Y-m-d');
                    $newChallan->challan_type = 'Readmission';
                    $newChallan->fee_month = $nextMonth->startOfMonth()->toDateString();
                    $newChallan->total_amount = $totalInst2;
                    $newChallan->issue_date = $issueDate;
                    $newChallan->due_date = $nextMonth->copy()->addWeek()->toDateString();
                    $newChallan->status = 'Issued';
                    $newChallan->owned_by = $existingChallan->owned_by;
                    $newChallan->created_by = $existingChallan->created_by;
                    $newChallan->session_id = $existingChallan->session_id;
                    $newChallan->concession_amount = $concessionInst2;
                    $newChallan->save();

                    foreach ($feeHeadsInst2 as $feeHead) {
                        $challanHead = new ChallanHead;
                        $challanHead->challan_id = $newChallan->id;
                        $challanHead->head_id = $feeHead->head_id;
                        $challanHead->price = $feeHead->price;
                        $challanHead->concession = $feeHead->concession;
                        $challanHead->save();
                        $feeHead['pord_id'] = $challanHead->id;
                    }
                    // dd($feeHeadsInst2);
                    $dataInst2 = [
                        'id' => $newChallan->id,
                        'no' => $newChallan->challanNo,
                        'date' => $newChallan->challan_date,
                        'reference' => $newChallan->student_id,
                        'category' => 'Readmission',
                        'user_id' => $newChallan->student_id,
                        'user_type' => 'Student',
                        'owned_by' => $newChallan->owned_by,
                        'created_by' => $newChallan->created_by,
                        'items' => [],
                    ];
                    foreach ($feeHeadsInst2 as $feeHead) {
                        $dataInst2['items'][] = [
                            'prod_id' => $feeHead->pord_id,
                            'head' => $feeHead->head_id,
                            'price' => $feeHead->price,
                            'quantity' => 1,
                            'concession' => $challanHead->concession,
                            'total' => $feeHead->price - $challanHead->concession,
                        ];
                    }
                    $dataret = Utility::jrentry($dataInst2);
                    $newChallan->voucher_id = $dataret;
                    $newChallan->save();
                }
            }

            DB::commit();

            return redirect()->route('readmissionchallanlist')->with('success', 'Installment challans have been created successfully');
            // return redirect()->back()->with('success', 'Installment challans have been created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

   public function destroy(Request $request)
{
    $challanNos = collect($request->rows)->pluck(2)->filter()->unique()->values()->toArray();

    if (empty($challanNos)) {
        return response()->json([
            'success' => false,
            'message' => 'No challans provided'
        ]);
    }

    try {
        DB::beginTransaction();

        $issuedIds = [];
        $journalIds = [];

        // 🔥 STEP 1: VALIDATE EVERYTHING FIRST (NO DELETE YET)
        $foundChallans = [];

        foreach (array_chunk($challanNos, 500) as $chunk) {

            $challans = DB::table('challans')
                ->whereIn('challanNo', $chunk)
                ->select('id', 'challanNo', 'status', 'voucher_id')
                ->get();

            foreach ($challans as $c) {
                $foundChallans[$c->challanNo] = $c;
            }
        }

        // 🔴 VALIDATION: ensure ALL exist
        if (count($foundChallans) !== count($challanNos)) {
            throw new \Exception('Some challans not found.');
        }

        // 🔴 VALIDATION: ensure ALL are issued
        foreach ($foundChallans as $c) {
            if (strtolower($c->status) !== 'issued') {
                throw new \Exception("Challan {$c->challanNo} is already paid.");
            }

            $issuedIds[] = $c->id;

            if ($c->voucher_id) {
                $journalIds[] = $c->voucher_id;
            }
        }

        $journalIds = array_unique($journalIds);

        // 🔥 STEP 2: DELETE IN CHUNKS (AFTER FULL VALIDATION)

        foreach (array_chunk($journalIds, 500) as $chunk) {
            DB::table('journal_items')
                ->whereIn('journal', $chunk)
                ->delete();

            DB::table('journal_entries')
                ->whereIn('id', $chunk)
                ->where('voucher_type', 'JV')
                ->delete();
        }

        foreach (array_chunk($issuedIds, 500) as $chunk) {
            DB::table('challans')
                ->whereIn('id', $chunk)
                ->delete();
        }

        // ✅ ONLY COMMIT AFTER ALL CHUNKS COMPLETE
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => count($issuedIds) . ' challans rolled back successfully.'
        ]);

    } catch (\Exception $e) {

        DB::rollBack(); // 🔥 FULL rollback (nothing deleted)

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
}

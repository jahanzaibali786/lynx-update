<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\ClassSection;
use App\Models\ClassWiseFee;
use App\Models\Concession;
use App\Models\ConcessionPolicyHead;
use App\Models\FeeHead;
use App\Models\Section;
use App\Models\Session;
use App\Models\StudentEnrollments;
use App\Models\StudentFeeRevisionBatch;
use App\Models\StudentFeeRevisionItem;
use App\Models\StudentFeeStructure;
use App\Models\StudentHistory;
use App\Models\StudentRegistration;
use App\Models\User;
use Illuminate\Http\Request;

class StudentPromotions extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $promotionType = $request->input('promotion_type', 'promotion');
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        }
        $session = Session::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('year', 'id');
        $session->prepend('Select Session', '');
        $branches->prepend('Select Branch', '');

        $branchFrom = $request->input('branches') ?: $request->input('branch_from');
        $branchTo = $promotionType === 'branch_promotion'
            ? $request->input('branch_to')
            : $branchFrom;

        $classesFrom = $branchFrom
            ? Classes::where('owned_by', $branchFrom)->get()->pluck('name', 'id')
            : collect();
        $classesTo = $branchTo
            ? Classes::where('owned_by', $branchTo)->get()->pluck('name', 'id')
            : $classesFrom;
        $classesFrom->prepend('Select Class', '');
        $classesTo->prepend('Select Class', '');

        $sectionsFrom = $request->filled('class_id')
            ? ClassSection::with('sectionName')->where('class_id', $request->class_id)->get()->pluck('SectionName.name', 'section_id')
            : collect();
        $sectionsTo = $request->filled('class_to')
            ? ClassSection::with('sectionName')->where('class_id', $request->class_to)->get()->pluck('SectionName.name', 'section_id')
            : collect();
        $sectionsFrom->prepend('Select Section', '');
        $sectionsTo->prepend('Select Section', '');

        $students = collect();
        if ($branchFrom && $request->filled('class_id')) {
            $students = StudentEnrollments::with(['StudentRegistration', 'class', 'section'])
                ->where('owned_by', $branchFrom)
                ->where('class_id', $request->class_id)
				->where('active_status', 1)
                ->when($request->filled('section_from'), fn($q) => $q->where('section_id', $request->section_from))
                ->when($request->filled('session_from_id'), fn($q) => $q->where('session_id', $request->session_from_id))
                ->when($request->filled('register_option') && $request->register_option != 'all', function ($q) use ($request) {
                    $q->whereHas('StudentRegistration', function ($studentQuery) use ($request) {
                        if ($request->register_option == 'shifa') {
                            $studentQuery->where('register_option', 2);
                        } else {
                            $studentQuery->where(function ($regularQuery) {
                                $regularQuery->where('register_option', '!=', 2)
                                    ->orWhereNull('register_option');
                            });
                        }
                    });
                })
                ->get();
        }

        $feeHeads = FeeHead::orderBy('id')->get();

        return view('students.promotions.index', compact(
            'branches',
            'classesFrom',
            'classesTo',
            'session',
            'feeHeads',
            'students',
            'sectionsFrom',
            'sectionsTo',
            'promotionType'
        ));
    }

    public function feeheads(Request $request)
    {
        try {
            \DB::beginTransaction();
            foreach ($request->feeData as $fee) {
                if(@$fee['head_id']){
                    $classfee = ClassWiseFee::where('owned_by',$request->branches)->where('session_id',$request->session_id)->where('class_id',$request->class_id)->find($fee['head_id']);
                    if($classfee){
                        $classfee->amount = $fee['amount'];
                        $classfee->save();
                    }
                }
            }
            \DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Classwise Fee Updated Successfully'
            ], 200);

        } catch (\Exception $e) {
            dd($e);
            \DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to Update ClassFee: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                            'filters.promotion_type' => 'required|in:promotion,branch_promotion',
                            'filters.branches' => 'required',
                            'filters.session_id' => 'required',
                            'filters.class_id' => 'required',
                            'filters.class_to' => 'required',
                            'studentData' => 'required|array',
                           ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return response()->json([
                'status' => 'error',
                'message' => $messages->first()
            ], 500);
        }
        try {
            \DB::beginTransaction();
            $filters = $request->input('filters', []);
            $promotionType = $filters['promotion_type'] ?? 'promotion';
            $branchFrom = $filters['branches'];
            $branchTo = $promotionType === 'branch_promotion'
                ? ($filters['branch_to'] ?? null)
                : $branchFrom;

            if (!$branchTo) {
                throw new \Exception('Branch To is required for branch promotion.');
            }

            $sessionTo = $filters['session_id'];
            $classFrom = $filters['class_id'];
            $classTo = $filters['class_to'];
            $feePercentages = collect($request->input('feePercentages', []))
                ->mapWithKeys(fn($row) => [(int) ($row['head_id'] ?? 0) => (float) ($row['percentage'] ?? 0)]);
            if ($feePercentages->isEmpty()) {
                throw new \Exception('Please check at least one fee head for revision.');
            }

            $feeHeads = FeeHead::whereIn('id', $feePercentages->keys()->filter()->all())
                ->orderBy('id')
                ->get();

            foreach ($request->studentData as $student) {
                $stud = StudentEnrollments::where('id', $student['student_id'] ?? null)->first()
                    ?: StudentEnrollments::where('enrollId', $student['enrollId'])->first();
                if (!$stud) {
                    continue;
                }

                $oldSession = $stud->session_id;
                $oldClass = $stud->class_id;
                $oldSection = $stud->section_id;
                $oldBranch = $stud->owned_by;
                $sectionTo = $student['section_to'] ?? ($filters['section_to'] ?? null);

                $promotion = new \App\Models\StudentPromotions();
                $promotion->student_id = $student['enrollId'];
                $promotion->prev_session = $oldSession;
                $promotion->new_session = $sessionTo;
                $promotion->class_from = $classFrom;
                $promotion->class_to = $classTo;
                $promotion->prev_section = $oldSection;
                $promotion->new_section = $sectionTo;
                $promotion->branch_from = $branchFrom;
                $promotion->branch_to = $branchTo;
                $promotion->promotion_date = date('Y-m-d');
                $promotion->owned_by = $branchTo;
                $promotion->created_by = \Auth::user()->creatorId();
                $promotion->save();

                $studentenroll = $stud;
                if ($studentenroll) {
                    $studentenroll->class_id = $classTo;
                    $studentenroll->section_id = $sectionTo;
                    $studentenroll->session_id = $sessionTo;
                    $studentenroll->owned_by = $branchTo;
                    $studentenroll->save();
                    $students = StudentRegistration::find($studentenroll->regId);
                    if ($students) {
                        $students->class_id = $classTo;
                        $students->session_id = $sessionTo;
                        $students->owned_by = $branchTo;
                        $students->save();
                    }
                }
                $his = new StudentHistory();
                $his->reg_id = $studentenroll->regId;
                $his->student_id = $studentenroll->enrollId;
                $his->event_type = $promotionType === 'branch_promotion' ? 'transfer' : 'promote';
                $his->from_session_id = $oldSession;
                $his->from_class_id = $oldClass;
                $his->to_class_id = $classTo;
                $his->from_section_id = $oldSection;
                $his->to_section_id = $sectionTo;
                $his->from_branch_id = $oldBranch;
                $his->to_branch_id = $branchTo;
                $his->to_session_id = $sessionTo;
                $his->effective_date = $promotion->promotion_date;
                $his->remarks = $promotionType === 'branch_promotion'
                    ? 'Student Branch Promotion'
                    : 'Student Promoted';
                $his->owned_by = $branchTo;
                $his->created_by = $studentenroll->created_by;
                $his->save();

                $batch = StudentFeeRevisionBatch::create([
                    'revision_type' => $promotionType,
                    'promotion_id' => $promotion->id,
                    'student_id' => $studentenroll->enrollId,
                    'reg_id' => $studentenroll->regId,
                    'session_from_id' => $oldSession,
                    'session_to_id' => $sessionTo,
                    'branch_from_id' => $oldBranch,
                    'branch_to_id' => $branchTo,
                    'class_from_id' => $oldClass,
                    'class_to_id' => $classTo,
                    'section_from_id' => $oldSection,
                    'section_to_id' => $sectionTo,
                    'effective_from' => $promotion->promotion_date,
                    'status' => 'applied',
                    'remarks' => $his->remarks,
                    'owned_by' => $branchTo,
                    'created_by' => \Auth::user()->creatorId(),
                ]);

                $activeConcession = Concession::where('student_id', $studentenroll->regId)
                    ->where('status', 'Approved')
                    ->where('active_status', 1)
                    ->where(function ($q) {
                        $q->where('end_date', '>=', date('Y-m-d'))
                            ->orWhereNull('end_date');
                    })
                    ->orderByDesc('id')
                    ->first();

                $concessionPolicyHeads = $activeConcession
                    ? ConcessionPolicyHead::where('concession_id', $activeConcession->concession_id)
                        ->get()
                        ->keyBy('head_id')
                    : collect();

                foreach ($feeHeads as $head) {
                    $stdfeestructure = StudentFeeStructure::where('reg_id', $studentenroll->regId)
                        ->where('head_id', $head->id)
                        ->where(function ($q) use ($oldBranch, $oldClass) {
                            $q->where('branch_id', $oldBranch)
                                ->orWhere('class_id', $oldClass);
                        })
                        ->first();

                    if (!$stdfeestructure) {
                        $stdfeestructure = StudentFeeStructure::where('reg_id', $studentenroll->regId)
                            ->where('head_id', $head->id)
                            ->first();
                    }

                    $prevBase = (float) ($stdfeestructure->amount ?? 0);
                    $prevDiscount = (float) ($stdfeestructure->discount ?? 0);
                    $prevChecked = (int) ($stdfeestructure->checked_status ?? 0);
                    $prevIsCustom = (int) ($stdfeestructure->is_custom ?? 0);
                    $percentage = (float) ($feePercentages->get($head->id, 0));
                    $newBase = round($prevBase + (($prevBase * $percentage) / 100));
                    $policyDiscount = $concessionPolicyHeads->has($head->id)
                        ? (float) $concessionPolicyHeads->get($head->id)->percentage
                        : $prevDiscount;
                    $newDiscount = $policyDiscount;
                    $newChecked = $prevChecked;
                    $newIsCustom = ($prevIsCustom || $percentage != 0) ? 1 : $prevIsCustom;
                    $prevPayable = round($prevBase - (($prevBase * $policyDiscount) / 100));
                    $newPayable = round($newBase - (($newBase * $policyDiscount) / 100));

                    if (!$stdfeestructure) {
                        $stdfeestructure = new StudentFeeStructure();
                        $stdfeestructure->reg_id = $studentenroll->regId;
                        $stdfeestructure->head_id = $head->id;
                        $stdfeestructure->discount = $newDiscount;
                        $stdfeestructure->checked_status = $newChecked;
                    }

                    $stdfeestructure->student_id = $studentenroll->enrollId;
                    $stdfeestructure->class_id = $classTo;
                    $stdfeestructure->branch_id = $branchTo;
                    $stdfeestructure->amount = $newBase;
                    $stdfeestructure->discount = $newDiscount;
                    $stdfeestructure->checked_status = $newChecked;
                    $stdfeestructure->is_custom = $newIsCustom;
                    $stdfeestructure->owned_by = $branchTo;
                    $stdfeestructure->created_by = \Auth::user()->creatorId();
                    $stdfeestructure->save();
                    //update class ,branch in student fee structure other heads.
                    StudentFeeStructure::where('reg_id', $studentenroll->regId)
                            ->update([
                                'owned_by' => $branchTo,
                                'class_id' => $classTo,
                                'branch_id' => $branchTo
                            ]);
                    
                    StudentFeeRevisionItem::create([
                        'batch_id' => $batch->id,
                        'student_fee_structure_id' => $stdfeestructure->id,
                        'student_id' => $studentenroll->enrollId,
                        'reg_id' => $studentenroll->regId,
                        'head_id' => $head->id,
                        'percentage' => $percentage,
                        'prev_base_amount' => $prevBase,
                        'new_base_amount' => $newBase,
                        'prev_payable_amount' => $prevPayable,
                        'new_payable_amount' => $newPayable,
                    ]);
                }
            }
            \DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Student successfully promoted.'
            ], 200);

        } catch (\Exception $e) {
            \DB::rollBack();
            dd($e);
            \Log::error('Error promoting student: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to promote student: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function bulkTuitionIncrement(Request $request)
    {
        if (!\Auth::user()->can('manage promotion')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $user = \Auth::user();
        $studentsQuery = StudentEnrollments::with(['StudentRegistration', 'class', 'section'])
            ->where('active_status', 1)
            ->whereHas('StudentRegistration', function ($query) {
                $query->where('register_option', 2);
            });

        if ($user->type === 'company') {
            $studentsQuery->where('created_by', $user->creatorId());
        } else {
            $studentsQuery->where('owned_by', $user->ownedId());
        }

        $studentCount = (clone $studentsQuery)->count();
        $tuitionHead = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', ['%tuition%'])->first();

        return view('students.promotions.bulk_tuition_increment', compact('studentCount', 'tuitionHead'));
    }

    public function bulkTuitionIncrementStore(Request $request)
    {
        if (!\Auth::user()->can('manage promotion')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'tuition_increment_percentage' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $user = \Auth::user();
        $percentage = (int) $request->input('tuition_increment_percentage', 0);
        $effectiveFrom = \Carbon\Carbon::today()->toDateString();
        $tuitionHead = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', ['%tuition%'])->first();

        if (!$tuitionHead) {
            return redirect()->back()->with('error', __('Tuition Fee head was not found.'));
        }

        $studentsQuery = StudentEnrollments::with(['StudentRegistration', 'class', 'section'])
            ->where('active_status', 1)
            ->whereHas('StudentRegistration', function ($query) {
                $query->where('register_option', 2);
            });

        if ($user->type === 'company') {
            $studentsQuery->where('created_by', $user->creatorId());
        } else {
            $studentsQuery->where('owned_by', $user->ownedId());
        }

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', __('No active Shifa students were found for bulk tuition increment.'));
        }

        try {
            \DB::beginTransaction();

            $processed = 0;
            $skipped = 0;

            foreach ($students as $stud) {
                $regId = $stud->regId;
                $studentId = $stud->enrollId;
                $branchId = $stud->owned_by;
                $classId = $stud->class_id;
                $sectionId = $stud->section_id;
                $sessionId = $stud->session_id;

                $alreadyApplied = StudentFeeRevisionBatch::where('revision_type', 'tuition_increment')
                    ->where('student_id', $studentId)
                    ->whereDate('effective_from', $effectiveFrom)
                    ->exists();

                if ($alreadyApplied) {
                    $skipped++;
                    continue;
                }

                $feeStructure = StudentFeeStructure::where('reg_id', $regId)
                    ->where('head_id', $tuitionHead->id)
                    ->first();

                if (!$feeStructure) {
                    $classFee = ClassWiseFee::where('class_id', $classId)
                        ->where('head_id', $tuitionHead->id)
                        ->where('owned_by', $branchId)
                        ->first();

                    if (!$classFee) {
                        $skipped++;
                        continue;
                    }

                    $feeStructure = new StudentFeeStructure();
                    $feeStructure->reg_id = $regId;
                    $feeStructure->student_id = $studentId;
                    $feeStructure->head_id = $tuitionHead->id;
                    $feeStructure->class_id = $classId;
                    $feeStructure->branch_id = $branchId;
                    $feeStructure->checked_status = (int) ($classFee->checked_status ?? 1);
                    $feeStructure->amount = (float) ($classFee->amount ?? 0);
                    $feeStructure->discount = (float) ($classFee->discount ?? 0);
                    $feeStructure->is_custom = 0;
                    $feeStructure->owned_by = $branchId;
                    $feeStructure->created_by = $user->creatorId();
                }

                $oldBase = (float) ($feeStructure->amount ?? 0);
                $oldDiscount = (float) ($feeStructure->discount ?? 0);
                $activeConcession = Concession::where('student_id', $regId)
                    ->where('status', 'Approved')
                    ->where('active_status', 1)
                    ->where(function ($q) {
                        $q->where('end_date', '>=', date('Y-m-d'))
                          ->orWhereNull('end_date');
                    })
                    ->orderByDesc('id')
                    ->first();

                $policyDiscount = $activeConcession
                    ? ConcessionPolicyHead::where('concession_id', $activeConcession->concession_id)
                        ->where('head_id', $tuitionHead->id)
                        ->value('percentage')
                    : null;

                $discount = $policyDiscount !== null ? (float) $policyDiscount : $oldDiscount;
                $newBase = round($oldBase + (($oldBase * $percentage) / 100), 2);
                $oldPayable = round($oldBase - (($oldBase * $discount) / 100), 2);
                $newPayable = round($newBase - (($newBase * $discount) / 100), 2);

                $promotion = new \App\Models\StudentPromotions();
                $promotion->student_id = $studentId;
                $promotion->prev_session = $sessionId;
                $promotion->new_session = $sessionId;
                $promotion->class_from = $classId;
                $promotion->class_to = $classId;
                $promotion->prev_section = $sectionId;
                $promotion->new_section = $sectionId;
                $promotion->branch_from = $branchId;
                $promotion->branch_to = $branchId;
                $promotion->promotion_date = $effectiveFrom;
                $promotion->owned_by = $branchId;
                $promotion->created_by = $user->creatorId();
                $promotion->save();

                $history = new StudentHistory();
                $history->reg_id = $regId;
                $history->student_id = $studentId;
                $history->event_type = 'tuition_increment';
                $history->from_session_id = $sessionId;
                $history->from_class_id = $classId;
                $history->from_section_id = $sectionId;
                $history->from_branch_id = $branchId;
                $history->to_session_id = $sessionId;
                $history->to_class_id = $classId;
                $history->to_section_id = $sectionId;
                $history->to_branch_id = $branchId;
                $history->effective_date = $effectiveFrom;
                $history->remarks = 'Bulk Tuition Increment';
                $history->owned_by = $branchId;
                $history->created_by = $user->creatorId();
                $history->save();

                $batch = StudentFeeRevisionBatch::create([
                    'revision_type' => 'tuition_increment',
                    'promotion_id' => $promotion->id,
                    'student_id' => $studentId,
                    'reg_id' => $regId,
                    'session_from_id' => $sessionId,
                    'session_to_id' => $sessionId,
                    'branch_from_id' => $branchId,
                    'branch_to_id' => $branchId,
                    'class_from_id' => $classId,
                    'class_to_id' => $classId,
                    'section_from_id' => $sectionId,
                    'section_to_id' => $sectionId,
                    'effective_from' => $effectiveFrom,
                    'status' => 'applied',
                    'remarks' => 'Bulk Tuition Increment',
                    'owned_by' => $branchId,
                    'created_by' => $user->creatorId(),
                ]);

                $feeStructure->student_id = $studentId;
                $feeStructure->class_id = $classId;
                $feeStructure->branch_id = $branchId;
                $feeStructure->amount = $newBase;
                $feeStructure->discount = $discount;
                $feeStructure->checked_status = (int) ($feeStructure->checked_status ?? 1);
                $feeStructure->is_custom = 1;
                $feeStructure->owned_by = $branchId;
                $feeStructure->created_by = $user->creatorId();
                $feeStructure->save();

                StudentFeeRevisionItem::create([
                    'batch_id' => $batch->id,
                    'student_fee_structure_id' => $feeStructure->id,
                    'student_id' => $studentId,
                    'reg_id' => $regId,
                    'head_id' => $tuitionHead->id,
                    'percentage' => $percentage,
                    'prev_base_amount' => $oldBase,
                    'new_base_amount' => $newBase,
                    'prev_payable_amount' => $oldPayable,
                    'new_payable_amount' => $newPayable,
                ]);

                $processed++;
            }

            \DB::commit();

            return redirect()
                ->route('student-promotion.bulk-tuition')
                ->with('success', __('Bulk tuition increment applied successfully. Processed: :processed, Skipped: :skipped', [
                    'processed' => $processed,
                    'skipped' => $skipped,
                ]));
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Bulk tuition increment failed: ' . $e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

}

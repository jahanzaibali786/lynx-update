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
                        $students->branch = $branchTo;
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
                    // dd($stdfeestructure);
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
}

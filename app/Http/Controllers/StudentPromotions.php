<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\ClassSection;
use App\Models\ClassWiseFee;
use App\Models\Section;
use App\Models\Session;
use App\Models\StudentEnrollments;
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
        // dd($request->all());
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $query = ClassWiseFee::where('created_by', '=', \Auth::user()->creatorId());
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $query = ClassWiseFee::where('owned_by', '=', \Auth::user()->ownedId());
        }
        $session = Session::where('active_status', '1')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('year', 'id');
        $session->prepend('Select Session', '');
        $branches->prepend('Select Branch', '');
        $students = [];
        if (!empty($request->branches)) {
            $classes = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
        } else {
            $classes = collect();
        }
        if (!empty($request->session_id)) {
            $query->where('session_id', '=', $request->session_id);
        }
        $classes->prepend('select Class', '');
        if (!empty($request->class_id)) {
            $students = StudentEnrollments::with('StudentRegistration')->where('class_id', $request->class_id)->get();
        }

        if (!empty($request->class_to)) {
            $query->where('class_id', $request->class_to);
            $classwisefee = $query->get();
            $section = ClassSection::with('sectionName')->where('class_id', $request->class_to)->get()->pluck('SectionName.name', 'section_id');
        } else {
            $section = collect();
            $classwisefee = collect();
        }

        return view('students.promotions.index', compact('branches', 'classes', 'session', 'classwisefee', 'students', 'section'));
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
        // dd($request->all());
        $validator = \Validator::make(
            $request->all(), [
                            'filters.branches' => 'required',
                            'filters.session_id' => 'required',
                            'filters.class_id' => 'required',
                            'filters.class_to' => 'required',
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
            foreach ($request->studentData as $student) {
                $stud = StudentEnrollments::where('enrollId',$student['enrollId'])->first();
                $promotion = new \App\Models\StudentPromotions();
                $promotion->student_id = $student['enrollId'];
                $promotion->prev_session = $stud->session_id;
                $promotion->new_session = $request->filters['session_id'];
                $promotion->class_from = $request->filters['class_id'];
                $promotion->class_to = $request->filters['class_to'];
                $promotion->prev_section = $stud->section_id;
                $promotion->new_section = $student['section_to'];
                $promotion->branch_from = $request->filters['branches'];
                $promotion->promotion_date = date('Y-m-d');
                $promotion->owned_by = $stud->owned_by;
                $promotion->created_by = \Auth::user()->creatorId();
                $promotion->save();

                $studentenroll = $stud;
                if ($studentenroll) {
                    $studentenroll->class_id = $request->filters['class_to'];
                    $studentenroll->section_id = $student['section_to'];
                    $studentenroll->session_id = $request->filters['session_id'];
                    $studentenroll->save();
                    $students = StudentRegistration::find($studentenroll->regId);
                    if ($students) {
                        $students->class_id = $request->filters['class_to'];
                        $students->session_id = $request->filters['session_id'];
                        $students->save();
                    }
                }
                $his = new StudentHistory();
                $his->reg_id = $studentenroll->regId;
                $his->student_id = $studentenroll->enrollId;
                $his->event_type = 'promote';
                $his->from_session_id = $promotion->prev_session;
                $his->from_class_id = $promotion->class_from;
                $his->to_class_id = $promotion->class_to;
                $his->from_section_id = $promotion->prev_section;
                $his->to_section_id = $promotion->new_section;
                $his->from_branch_id = $promotion->owned_by;
                $his->to_branch_id = $promotion->owned_by;
                $his->to_session_id = $promotion->new_session;
                $his->effective_date = $promotion->promotion_date;
                $his->remarks = 'Student Promoted';
                $his->owned_by = $studentenroll->owned_by;
                $his->created_by = $studentenroll->created_by;
                $his->save();
                $classfee = ClassWiseFee::where('owned_by', '=', $request->filters['branches'])->where('session_id', $request->filters['session_id'])->where('class_id',$request->filters['class_to'])->get();
                foreach ($classfee as $fee) {
                    $stdfeestructure = StudentFeeStructure::where('reg_id', $studentenroll->regId)->where('head_id', $fee->head_id)->where('branch_id',$request->filters['branches'])->where('class_id',$request->filters['class_id'])->first();
                    if ($stdfeestructure) {
                        $stdfeestructure->student_id = $student['enrollId'] ?? 0;
                        $stdfeestructure->class_id = $request->filters['class_to'];
                        $stdfeestructure->amount = $fee->amount;
                        $stdfeestructure->save();
                    }
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

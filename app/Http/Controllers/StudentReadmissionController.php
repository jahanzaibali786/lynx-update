<?php

namespace App\Http\Controllers;

use App\Models\ChallanHead;
use App\Models\Challans;
use App\Models\Classes;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Session;
use App\Models\StudentFeeStructure;
use App\Models\FeeHead;
use App\Models\Concession;
use App\Models\StudentEnrollments;
use App\Models\StudentHistory;
use App\Models\StudentRegistration;
use App\Models\StudentTransfer;
use App\Models\ChartOfAccount;
use App\Models\StudentWithdrawal;
use App\Models\StudentReadmission;
use App\Models\ConcessionPolicyHead;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Auth;
use DB;

class StudentReadmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // if(\Auth::user()->can('manage session'))
        // {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $query = StudentReadmission::with('student')->where('created_by',Auth::user()->creatorId());
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $query = StudentReadmission::with('student')->where('owned_by',Auth::user()->ownedId());
            }
            $studenttransfer = $query->orderBy('id','Desc')->get();
            return view('students.student_readmission.index', compact('studenttransfer'));
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // if(\Auth::user()->can('create session'))
        // {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }
                $session = Session::where('created_by', \Auth::user()->creatorId())->get()->pluck('year', 'id');
                $session->prepend('Select Session','');
            return view('students.student_readmission.create',compact('branches','session'));
        // }
        // else
        // {
        //     return response()->json(['error' => __('Permission denied.')], 401);
        // }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all(), now()->subDays(30));
        // if(\Auth::user()->can('create session'))
        // {
            $validator = \Validator::make(
                $request->all(),[
                    'student_id' => 'required',
                    'branch_id' => 'required',
                    'class_id' => 'required',
                    'session_id' => 'required',
                    'readmission_date' => 'required|date',
                    'month_date' => 'required|date',
                    'issue_date' => 'required|date',
                    'due_date' => 'required|date',
                    'reason' => 'required|string',
                ]
            );


            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            DB::beginTransaction();
            try {
                $total = 0;
                $concession = 0;
                $item = [];
                $session = Session::orderBy('id','Desc')->where('active_status','1')->where('created_by', '=', \Auth::user()->creatorId())->first();
                $std_enroll = StudentEnrollments::where('enrollId', $request->student_id)->first();

                $withdrawal = StudentWithdrawal::where('student_id',$std_enroll->regId)->orderBy('id', 'desc')->first();
                if(!$withdrawal){
                    return redirect()->back()->with('error', 'Student is not withdrawn yet.');
                }
                // dd($withdrawal->withdraw_date , now()->subDays(30));
                if($withdrawal && $withdrawal->withdraw_date >= now()->subDays(30)){
                }elseif($withdrawal && $withdrawal->withdraw_date >= now()->subDays(90)){
                    $challan = new Challans();
                    $challan->student_id = $std_enroll->regId;
                    $challan->class_id = $request->class_id;
                    $challan->rollno = $std_enroll->enrollId;
                    $challan->challanNo =  $this->challanNo();
                    $challan->challan_date = $request->readmission_date;
                    $challan->challan_type = 'Readmission';
                    $challan->fee_month = date('Y-m-01', strtotime($request->month_date));
                    $challan->issue_date = $request->issue_date;
                    $challan->due_date = $request->due_date;
                    $challan->status = 'Issued';
                    $challan->session_id = $session->id;
                    $challan->owned_by = $std_enroll->owned_by;
                    $challan->created_by = \Auth::user()->creatorId();
                    $challan->save();

                    $concessionAmount=0;
                    $itemIndex = 0;

                    $pattern = '%RE-ADMISSION%';
                    $adm_fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
                    $fee = StudentFeeStructure::where('head_id', $adm_fee_head->id)->where('reg_id',$std_enroll->regId)->orderBy('id','Desc')->first();
                    $challan_head = new ChallanHead();
                    $challan_head->challan_id = $challan->id;
                    $challan_head->head_id = $adm_fee_head->id;
                    $challan_head->price = $fee ? $fee->amount : 0;
                    $challan_head->concession =  round(($fee->amount / 100) * $fee->discount);
                    $challan_head->save();

                    $total += $fee->amount;
                    $item[$itemIndex]['prod_id'] = $challan_head->id;
                    $item[$itemIndex]['head'] = $adm_fee_head->id;
                    $item[$itemIndex]['price'] = $fee? $fee->amount : 0 ;
                    $item[$itemIndex]['quantity'] = 1;
                    $item[$itemIndex]['concession'] = round(($fee->amount / 100) * $fee->discount);
                    $item[$itemIndex]['total'] = $total ;
                    $itemIndex++;
                    $concession += round(($fee->amount / 100) * $fee->discount);
                    // Monthly fee head values
                    $concessionData = Concession::with('concession')
                    ->where('student_id', @$challan->student_id)->where('end_date', '>=', date('Y-m-d'))
                    ->where('status', 'Approved')->orderBy('id', 'desc')->first();
                        if (!$concessionData) {
                            $concessionData = Concession::with('concession')
                                ->where('student_id', @$challan->student_id)
                                ->where('status', 'Approved')
                                ->orderBy('id', 'desc')
                                ->first();
                        }

                    $fee_heads = StudentFeeStructure::with('feehead')->where('reg_id', $std_enroll->regId)->where('checked_status', 1)->where('owned_by', $std_enroll->owned_by)->get();
                    foreach ($fee_heads as $fee_head) {
                        if (strpos(strtolower($fee_head->feehead->fee_head), 'security') !== false || strpos(strtolower($fee_head->feehead->fee_head), 're-admission') !== false ||
                            strpos(strtolower($fee_head->feehead->fee_head), 'transfer') !== false || strpos(strtolower($fee_head->feehead->fee_head), 'admission') !== false) {
                            continue;
                        }
                        $iconcessionAmount = 0;
                        if ($concessionData) {
                            $concessionHead = ConcessionPolicyHead::where('head_id', $fee_head->head_id)
                                ->where('concession_id', $concessionData->concession_id)->first();
                            if ($concessionHead) {
                                $concessionAmount = round(($fee_head->amount / 100) * $concessionHead->percentage);
                            }else{
                                $concessionAmount = round(($fee_head->amount / 100) * $fee_head->discount);
                            }
                        }
                        $challan_head = new ChallanHead();
                        $challan_head->challan_id = $challan->id ?? 0;
                        $challan_head->head_id = $fee_head->head_id;
                        $challan_head->price = $fee_head->amount - $concessionAmount;
                        $challan_head->concession = $concessionAmount;
                        $challan_head->save();

                        $item[$itemIndex]['prod_id'] = $challan_head->id;
                        $item[$itemIndex]['head'] = $fee_head->head_id;
                        $item[$itemIndex]['price'] = $fee_head->amount;
                        $item[$itemIndex]['quantity'] = 1;
                        $item[$itemIndex]['concession'] = $concessionAmount;
                        $item[$itemIndex]['total'] = $fee_head->amount;
                        $itemIndex++;
                        $concession += $concessionAmount;
                        $total += $fee_head->amount - $concessionAmount;
                    }
                    $challan->concession_amount = $concession;
                    $challan->total_amount = $total;
                    $challan->save();

                }else{
                }

                $transfer = new StudentReadmission();
                $transfer->student_id = $std_enroll->enrollId;
                $transfer->challan_id = @$challan->id;
                $transfer->readmission_date = $request->readmission_date;
                $transfer->branch_id = $request->branch_id;
                $transfer->class_id = $request->class_id;
                $transfer->remarks = $request->reason;
                $transfer->session_id = $request->session_id;
                $transfer->owned_by = $std_enroll->owned_by;
                $transfer->created_by = \Auth::user()->creatorId();
                $transfer->save();
                //student history
                $stdhistory = new StudentHistory();
                $stdhistory->reg_id = $std_enroll->regId;
                $stdhistory->student_id = $std_enroll->enrollId;
                $stdhistory->event_type = 'Readmission';
                $stdhistory->from_session_id = $withdrawal->session_id;
                $stdhistory->from_class_id = $withdrawal->class_id;
                $stdhistory->from_branch_id = $withdrawal->branch_id;
                $stdhistory->to_session_id = $request->session_id; 
                $stdhistory->to_class_id = $request->class_id;
                $stdhistory->to_branch_id = $request->branch_id;
                $stdhistory->effective_date = $request->readmission_date;
                $stdhistory->remarks = $request->reason;
                $stdhistory->owned_by = $std_enroll->owned_by;
                $stdhistory->created_by = \Auth::user()->creatorId();
                $stdhistory->save();

                if(@$challan){
                    $data['id'] =$challan->id;
                    $data['date'] =$challan->challan_date;
                    $data['no'] =$challan->challanNo;
                    $data['reference'] =$std_enroll->regId;
                    $data['category'] = 'Readmission';
                    $data['user_id'] =$std_enroll->regId;
                    $data['user_type'] ='Student';
                    $data['owned_by'] =$challan->owned_by;
                    $data['created_by'] =$challan->created_by;
                    $data['items'] =$item;

                    $dataret  = Utility::jrentry($data);
                    $challan->voucher_id = $dataret;
                    $challan->save();
                    //update status of enrollment and registration table
                    // $std_enroll->active_status = 1;
                    $std_enroll->save();

                    $std_registration = StudentRegistration::find($std_enroll->regId);
                    $std_registration->student_status = 'Enrolled';
                    // $std_registration->active_status = 1;
                    $std_registration->save();
                    
                }
                DB::commit();
                return redirect()->route('readmissionstudent.index')->with('success', 'Student Re-Admission has been created successfully.');
            } catch (\Exception $e) {
                DB::rollback();
                dd($e);
                return redirect()->back()->with('error', $e);
            }
            // }
            // else
            // {
            //     return redirect()->back()->with('error', 'Permission denied.');
            // }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StudentTransfer  $studentTransfer
     * @return \Illuminate\Http\Response
     */
    public function show(StudentTransfer $studentTransfer)
    {
        //
    }

    function challanNo()
    {
         $latest = Challans::where('created_by', '=', \Auth::user()->creatorId())->orderBY('id', 'desc')->first();
        // } else {
        if (!$latest) {
            return 1;
        }
        // }
        return $latest->challanNo + 1;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StudentTransfer  $studentTransfer
     * @return \Illuminate\Http\Response
     */
    public function edit(StudentReadmission $StudentReadmission,$id)
    {
         // if(\Auth::user()->can('edit session'))
        // {
            $StudentReadmission = StudentReadmission::with('challan')->find($id);
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }

                $class = Classes::where('id', '=',$StudentReadmission->class_id)->pluck('name', 'id');
                $session = Session::where('id', '=',$StudentReadmission->session_id)->pluck('year', 'id');
        
                $std = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('roll_no',$StudentReadmission->student_id)->pluck('stdname', 'roll_no');

            return view('students.student_readmission.edit',compact('branches','StudentReadmission','class','std','session'));
        // }
        // else
        // {
        //     return response()->json(['error' => __('Permission denied.')], 401);
        // }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StudentTransfer  $studentTransfer
     * @return \Illuminate\Http\Response
     */
    //  make this function for readmission update function
    public function update(Request $request, StudentReadmission $StudentReadmission,$id)
    {
        // dd($request->all());
        // if(\Auth::user()->can('create session'))
        // {
            $validator = \Validator::make(
                $request->all(),[
                    'student_id' => 'required',
                    'readmission_date' => 'required|date',
                    'branch_id' => 'required',
                    'class_id' => 'required',
                    'session_id' => 'required',
                    'month_date' => 'required|date',
                    'issue_date' => 'date',
                    'due_date' => 'date',
                    'reason' => 'required|string',
                ]
            );


            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            DB::beginTransaction();
            try {
                $total = 0;
                $concession = 0;
                $session = Session::orderBy('id','Desc')->where('active_status','1')->where('created_by', '=', \Auth::user()->creatorId())->first();
                $std_enroll = StudentEnrollments::where('enrollId', $request->student_id)->first();
                $readmission = StudentReadmission::with('challan')->find($id);
                $readmission->readmission_date = $request->readmission_date;
                $readmission->branch_id = $request->branch_id;
                $readmission->class_id = $request->class_id;
                $readmission->remarks = $request->reason;
                $readmission->session_id = $request->session_id;
                $readmission->save();

                if(!Empty($readmission->challan_id)){
                    $challan = Challans::where('id', $readmission->challan_id)->first();
                    $challan->challan_date = $request->readmission_date;
                    $challan->fee_month = date('Y-m-01', strtotime($request->month_date));
                    $challan->issue_date = $request->issue_date;
                    $challan->due_date = $request->due_date;
                    $challan->save();
                }else{
                }

                DB::commit();
                return redirect()->route('readmissionstudent.index')->with('success', 'Student Re-Admission has been updated successfully.');
            } catch (\Exception $e) {
                DB::rollback();
                dd($e);
                return redirect()->back()->with('error', $e);
            }
            // }
            // else
            // {
            //     return redirect()->back()->with('error', 'Permission denied.');
            // }
    }




    // public function update(Request $request, StudentTransfer $studentTransfer,$id)
    // {

    //        // if(\Auth::user()->can('create session'))
    //         // {
    //             $validator = \Validator::make(
    //                 $request->all(),[
    //                     'student_id' => 'required',
    //                     'transfer_date' => 'required|date',
    //                     'transfer_type' => 'required|string',
    //                     'branch_from' => 'required',
    //                     'class_from' => 'required',
    //                     'branch_to' => 'required',
    //                     'class_to' => 'required',
    //                     'section_to' => 'required',
    //                     'issue_date' => 'date',
    //                     'due_date' => 'date',
    //                     'reason' => 'required|string',
    //                 ]
    //             );


    //         if($validator->fails())
    //         {
    //             $messages = $validator->getMessageBag();
    //             return redirect()->back()->with('error', $messages->first());
    //         }
    //         DB::beginTransaction();
    //         try {
    //             $total = 0;
    //             $concession = 0;
    //             $session = Session::orderBy('id','Desc')->where('active_status','1')->where('created_by', '=', \Auth::user()->creatorId())->first();
    //             $std_enroll = StudentEnrollments::where('enrollId', $request->student_id)->first();

    //             $transfer = StudentTransfer::with('challan')->find($id);
    //             $transfer->transfer_date = $request->transfer_date;
    //             $transfer->transfer_type = $request->transfer_type;
    //             $transfer->branch_to = $request->branch_to;
    //             $transfer->class_to = $request->class_to;
    //             $transfer->section_to = $request->section_to;
    //             $transfer->reason = $request->reason;
    //             // $transfer->owned_by = \Auth::user()->ownedId();
    //             // $transfer->created_by = \Auth::user()->creatorId();
    //             $transfer->save();

    //             if($request->transfer_type == 'inter branch'){
    //                 if(!Empty($request->challan_id)){
    //                     $challan = Challans::where('id', $request->challan_id)->first();
    //                     $challan->issue_date = $request->issue_date;
    //                     $challan->due_date = $request->due_date;
    //                     $challan->save();
    //                 }else{
    //                     $pattern = '%transfer%';
    //                     $adm_fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
    //                     $fee = StudentFeeStructure::where('head_id', $adm_fee_head->id)->where('reg_id',$std_enroll->regId)->orderBy('id','Desc')->first();
    //                     $challan = new Challans();
    //                     $challan->student_id = $request->student_id;
    //                     $challan->class_id = $request->class_from;
    //                     $challan->rollno = $std_enroll->enrollId;
    //                     $challan->challanNo =  $this->challanNo();
    //                     $challan->challan_date = $request->transfer_date;
    //                     $challan->challan_type = 'Transfer';
    //                     $challan->issue_date = $request->issue_date;
    //                     $challan->due_date = $request->due_date;
    //                     $challan->status = 'Issued';
    //                     $challan->session_id = $session->id;
    //                     $challan->owned_by = $std_enroll->owned_by;
    //                     $challan->created_by = \Auth::user()->creatorId();
    //                     $challan->save();

    //                     $concessionAmount=0;
    //                     $itemIndex = 0;

    //                     $pattern = '%transfer%';
    //                     $adm_fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
    //                     $fee = StudentFeeStructure::where('head_id', $adm_fee_head->id)->where('reg_id',$request->student_id)->orderBy('id','Desc')->first();
    //                     $challan_head = new ChallanHead();
    //                     $challan_head->challan_id = $challan->id;
    //                     $challan_head->head_id = $adm_fee_head->id;
    //                     $challan_head->price = $fee ? $fee->amount : 0;
    //                     $challan_head->concession =  round(($fee->amount / 100) * $fee->discount);
    //                     $challan_head->save();

    //                     $total += $fee->amount;
    //                     $concession += round(($fee->amount / 100) * $fee->discount);
    //                     $item[$itemIndex]['prod_id'] = $challan_head->id;
    //                     $item[$itemIndex]['head'] = $adm_fee_head->id;
    //                     $item[$itemIndex]['price'] = $fee? $fee->amount : 0 ;
    //                     // $item[$itemIndex]['quantity'] = $concessiondata ? $concessiondata->concession_id : '';
    //                     $item[$itemIndex]['quantity'] = 1;
    //                     $item[$itemIndex]['concession'] = round(($fee->amount / 100) * $fee->discount);
    //                     $item[$itemIndex]['total'] = $total ;
    //                     $itemIndex++;

    //                     $challan->total_amount = $total;
    //                     $challan->save();
    //                     $transfer->challan_id = @$challan->id;
    //                     $transfer->save();

    //                     $data['id'] =$challan->id;
    //                     $data['date'] =$challan->challan_date;
    //                     $data['reference'] =$std_enroll->regId;
    //                     $data['category'] = 'Transfer';
    //                     $data['user_id'] =$std_enroll->regId;
    //                     $data['user_type'] ='Student';
    //                     $data['owned_by'] =$challan->owned_by;
    //                     $data['created_by'] =$challan->created_by;
    //                     $data['items'] =$item;

    //                     $dataret  = Utility::jrentry($data);
    //                     $challan->voucher_id = $dataret;
    //                     $challan->save();
    //                 }
    //             }else{
    //                 if(!Empty($request->challan_id)){
    //                     $challan = Challans::where('id', $request->challan_id)->first();
    //                     if($challan->status == 'Issued'){
    //                         $jour = JournalEntry::where('id',$challan->voucher_id)->where('category','Transfer')->where('user_id',$std_enroll->regId)->first();
    //                         JournalItem::where('journal',$jour->id)->delete();
    //                         $jour->delete();
    //                         $challan->delete();
    //                         $transfer->challan_id = '';
    //                         $transfer->save();
    //                     }else{
    //                         return redirect()->route('transferstudent.index')->with('error', 'Student Transfer Challan Recipt Delete First.');
    //                     }
    //                 }
    //             }

    //             DB::commit();
    //             return redirect()->route('transferstudent.index')->with('success', 'Student Transfer has been updated successfully.');
    //             } catch (\Exception $e) {
    //                 DB::rollback();
    //                 dd($e);
    //                 return redirect()->back()->with('error', $e);
    //             }
    //         // }
    //         // else
    //         // {
    //         //     return redirect()->back()->with('error', 'Permission denied.');
    //         // }
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StudentTransfer  $studentTransfer
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudentTransfer $studentTransfer)
    {
        //
    }

     public function changeStatus($id,$status)
    {

        $concession = StudentReadmission::find($id);
        $concession->status = $status;
        $concession->save();
        if($status == 'approved'){
            $readmission = StudentReadmission::where('id', $id)->first();
            $students = StudentRegistration::where('roll_no',$readmission->student_id)
                ->update(['class_id'=>$readmission->class_id,
                          'student_status'=>'Enrolled',
                          'active_status'=>1,
                          'session_id'=>$readmission->session_id,
                          'owned_by'=>$readmission->branch_id,]);
            $std_enroll = StudentEnrollments::where('enrollId', $readmission->student_id)
                ->update(['owned_by'=>$readmission->branch_id,
                        'active_status'=>1,
                          'class_id'=>$readmission->class_id,
                          'session_id'=>$readmission->session_id,]);
        return redirect()->route('readmissionstudent.index')->with('success', 'Student Re-Admission has been approved successfully.');
        }

        return redirect()->route('readmissionstudent.index')->with('success', 'Application Status Changed Successfully.');
    }

    public function readmission(Request $request)
    {
        // $student = DB::table('student_enrollments')->join('student_registrations', 'student_enrollments.regId', '=', 'student_registrations.id')
        // ->where('student_enrollments.class_id',$request->class_id)->get();
        $student = StudentRegistration::where('class_id',$request->class_id)->whereNotIn('student_status',['Enrolled','Registered'])->get();

        return response()->json(['student' => $student]);
    }



    public function transferapplication($id){
        $studenttransfer = StudentTransfer::where('id', $id)
        ->with('student', 'branchfrom','challan', 'classfrom','classto','branchto','sectionfrom','sectionto')
        ->where('created_by', Auth::user()->creatorId())
        ->first();
        $concessiondata = Concession::where('student_id', @$studenttransfer->student->id)->where('end_date', '>=', date('Y-m-d'))->orderBy('id', 'Desc')->where('status', 'Approved')->first();
            if(!$concessiondata){
                $concessiondata = Concession::with('concession')->where('student_id', @$studenttransfer->student->id)
                ->orderBy('id', 'desc')->whereNull( 'end_date')->where('status', 'Approved')->first();
            }
        $classfee = StudentFeeStructure::with('feehead')->where('reg_id', @$studenttransfer->student->id)->get()->pluck('feehead.fee_head','head_id');
        return view('students.student_transfer.application', compact('studenttransfer','concessiondata','classfee'));
    }

    public function adminsend($id){
        $studenttransfer = StudentTransfer::where('id', $id)->update(['status'=>'pending']);
        return redirect()->route('transferstudent.index')->with('success', 'Application Send to Head Office.');
    }

    // public function changeStatus($id,$status){
    //     if($status == 'approved'){
    //         $studenttransfer = StudentTransfer::where('id', $id)->first();
    //         // dd($studenttransfer);
    //         StudentTransfer::where('id', $id)->update(['status'=>'approved']);
    //         $students = StudentRegistration::where('roll_no',$studenttransfer->student_id)
    //             ->update(['branch'=>$studenttransfer->branch_to,
    //                       'class_id'=>$studenttransfer->class_to,
    //                       'owned_by'=>$studenttransfer->branch_to,]);
    //         $std_enroll = StudentEnrollments::where('enrollId', $studenttransfer->student_id)
    //             ->update(['owned_by'=>$studenttransfer->branch_to,
    //                       'class_id'=>$studenttransfer->class_to,
    //                       'section_id'=>$studenttransfer->section_to,]);
    //         return redirect()->route('transferstudent.index')->with('success', 'Application Approved.');
    //     }elseif($status == 'rollback'){
    //         $studenttransfer = StudentTransfer::where('id', $id)->update(['status'=>'rollback']);
    //         return redirect()->route('transferstudent.index')->with('success', 'Application Rollback.');
    //     }else{
    //         $studenttransfer = StudentTransfer::where('id', $id)->update(['status'=>'rejected']);
    //         return redirect()->route('transferstudent.index')->with('success', 'Application Rejected.');
    //     }
    //     return redirect()->route('transferstudent.index')->with('success', 'Application Send to Head Office.');

    // }

    public function calculateBalance(Request $request)
    {
        try{
        $id = $request->input('student_id');
        $student = StudentRegistration::where('roll_no',$id)->first();
        $admissionChallan = Challans::where('student_id', $id)
            ->whereRaw('LOWER(challan_type) LIKE ?', [strtolower('%admission%')])
            ->first();
        if (!$admissionChallan) {
            return response()->json(['error' => 'Admission challan not found.']);
        }
        $trasf = StudentTransfer::where('id',$request->transfer_id)->first();
        if (!empty($trasf->challan_id)) {
            $transfer_challan = Challans::where('student_id', $id)->where('id',$trasf->challan_id)->first();
            $transfer_fee = $transfer_challan->total_amount - ($transfer_challan->paid_amount + $transfer_challan->concession_amount);
        }

        $challanId = Challans::where('student_id', $id)->whereRaw('LOWER(challan_type) LIKE ?', [strtolower('%admission%')])->get()->pluck('id');
        $head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower('%security%')])->first();
        $challanHead = ChallanHead::with('challan')->whereIn('challan_id', $challanId)
            ->where('head_id', $head->id)->first();
            // dd($challanHead,$head->id,$head);
        if (!$challanHead) {
            return response()->json(['error'=>'No Security head attached while Registration to this student.']);
        }
        $journal = JournalEntry::whereIn('reference_id', $challanId)->get();
        // $journalItems = JournalItem::where('head', $challanHead->head_id)
        //     ->whereRaw('description REGEXP ?', ['Reciveable of Challan id : [0-9]+'])->get()->dd();
        $journalItems = JournalItem::where('head', $challanHead->head_id)
            ->whereIn('journal', $journal->pluck('id'))->get();

        $receivable = 0;
        $securityDeposit = 0;
        $netbalance= 0;
        foreach ($journalItems as $item) {
            if (preg_match('/Reciveable of Challan id : (\d+)/', $item->description, $matches)) {
                $extractedChallanId = $matches[1];
                if ($extractedChallanId == $challanId) {
                    if ($item->debit == 0) {
                        $securityDeposit = $item->credit;
                    } elseif ($item->credit == 0) {
                        $receivable += $item->debit;
                        $netbalance=$receivable;
                    }
                }
            }
        }

        $arrearsTotal = Challans::where('student_id', $student->id)
        ->where('status', '!=', 'Paid')->where('challan_type','!=','Transfer')
        ->whereMonth('challan_date', '<=', date('m'))
        ->whereYear('challan_date', '<=', date('Y'))
        ->select(\DB::raw('SUM(total_amount - (paid_amount + concession_amount)) AS arrears_total'))
        ->value('arrears_total');


        return response()->json([
            'actual_fee' => $arrearsTotal,
            'security_deposit' => $securityDeposit,
            'other_fee' => 0,
            'transfer_fee' => @$transfer_fee ?? 0,
            'total_receivables' => (($arrearsTotal + @$transfer_fee)),
            'net_balance' => $netbalance,
        ]);
    }catch (\Exception $e) {
        return response()->json(['error'=>$e]);
    }
    }
    public function challanheadadd(Request $request)
    {
        DB::beginTransaction();
        try{
            $studenttransfer = StudentTransfer::where('id', $request->transfer_id)->first();
            if($studenttransfer && $studenttransfer->challan_id){
                $challan=Challans::where('id',$studenttransfer->challan_id)->first();
                $tot_val = $challan->total_amount;
                if($challan && count($request->fee_head) > 0){
                    $journal = JournalEntry::where('id', '=', @$challan->voucher_id)->where('voucher_type', 'JV')->first();
                    for ($i=0; $i<count($request->fee_head); $i++) {
                        // dd($feeHead);
                        $challanHead = new ChallanHead();
                        $challanHead->challan_id = $challan->id;
                        $challanHead->head_id = $request->fee_head[$i];
                        $challanHead->price = $request->val[$i];
                        $challanHead->concession = 0;
                        $challanHead->save();

                        $tot_val += $challanHead->price;

                        $head_id = FeeHead::where('id', $request->fee_head[$i])->first();
                        $account_name = ChartOfAccount::where('id', $head_id->account_id)->first();
                        $journalItem = new JournalItem();
                        $journalItem->journal = $journal->id;
                        $journalItem->account = @$account_name->id;
                        $journalItem->head = $request->fee_head[$i];
                        $journalItem->description = $account_name->name;
                        $journalItem->entry_id = @$challanHead->id;
                        $journalItem->types = 'Challan';
                        $journalItem->credit = $request->val[$i];
                        $journalItem->debit = 0;
                        $journalItem->save();

                        //  reciveable entry
                        $account_recive = ChartOfAccount::where('id', $head_id->receivable_account_id)->first();
                        $journalItem = new JournalItem();
                        $journalItem->journal = $journal->id;
                        $journalItem->account = @$account_recive->id;
                        $journalItem->head = $request->fee_head[$i];
                        $journalItem->description = 'Reciveable of Challan id : ' . $challan->challanNo;
                        $journalItem->entry_id = @$challanHead->id;
                        $journalItem->types = 'Challan';
                        $journalItem->credit = 0;
                        $journalItem->debit = $request->val[$i];
                        $journalItem->save();

                    }
                    $challan->total_amount =$tot_val;
                    $challan->save();
                }
            }else{
                return response()->json(['success' => 'error', 'data' => 'Challan Not Found.']);
            }
            DB::commit();
            return response()->json(['success' => 'success', 'data' => 'Challan has been updated.']);
    }catch (\Exception $e) {
        dd($e);
        DB::rollback();
        return response()->json(['error'=>$e]);
    }
    }

}

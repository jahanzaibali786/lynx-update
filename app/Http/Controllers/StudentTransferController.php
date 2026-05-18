<?php

namespace App\Http\Controllers;

use App\Models\ChallanHead;
use App\Models\Challans;
use App\Models\Classes;
use App\Models\ClassWiseFee;
use App\Models\Concession;
use App\Models\Session;
use App\Models\FeeHead;
use App\Models\JournalEntry;
use App\Models\StudentEnrollments;
use App\Models\StudentRegistration;
use App\Models\StudentTransfer;
use App\Models\ChartOfAccount;
use App\Models\StudentFeeStructure;
use App\Models\JournalItem;
use App\Models\StudentHistory;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Auth;
use DB;
use Dompdf\Dompdf;
use Dompdf\Options;

class StudentTransferController extends Controller
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
            $branches->prepend('Select Branch', '');
            $query = StudentTransfer::with('student', 'branchfrom', 'branchto')->where('created_by', \Auth::user()->creatorId());
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            // $branches->prepend('Select Branch', '');
            $query = StudentTransfer::with('student', 'branchfrom', 'branchto')->where('owned_by', '=', \Auth::user()->ownedId());
        }
        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
        }
        if (!empty($request->status)) {
            $query->where('status', '=', $request->status);
        }
        if (!empty($request->start_date)) {
            $query->whereDate('transfer_date', '>', $request->start_date);
        }
        if (!empty($request->end_date)) {
            $query->whereDate('transfer_date', '<', $request->end_date);
        }
        $currentYear = date('Y');
        $currentMonth = date('m');
        $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
        $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
        if (empty($request->start_date) || empty($request->end_date)) {

            $request->merge(['start_date' => $dateFrom]);
            $request->merge(['end_date' => $dateTo]);
            $query->whereBetween('transfer_date', [$dateFrom, $dateTo]);
        }

        $studenttransfer = $query->orderBy('id', 'Desc')->get();
        $status = [
            ''   => 'All',
            'Draft'   => 'Draft',
            'Approved' => 'Approved',
            'Rejected'  => 'Rejected',
            'Roll Back'  => 'Roll Back',
        ];
        // $studenttransfer = StudentTransfer::with('student','branchfrom','branchto')->where('created_by',Auth::user()->creatorId())->paginate(50);
        return view('students.student_transfer.index', compact('studenttransfer', 'branches', 'status','dateFrom', 'dateTo'));
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
        $allbranches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $allbranches->prepend('Select Branch', '');
        return view('students.student_transfer.create', compact('branches','allbranches'));
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
    // Validation rules
    $rules = [
        'student_id' => 'required',
        'transfer_date' => 'required|date',
        'transfer_type' => 'required|string|in:intra-city,inter-city',
        'branch_from' => 'required',
        'class_from' => 'required',
        'branch_to' => 'required',
        'class_to' => 'required',
        'section_to' => 'required',
        'reason' => 'required|string',
    ];
    
    // Add challan field validation only for inter-city transfers
    if ($request->transfer_type === 'inter-city') {
        $rules['issue_date'] = 'required|date';
        $rules['due_date'] = 'required|date';
    }
    
    $validator = \Validator::make($request->all(), $rules);

    if($validator->fails())
    {
        $messages = $validator->getMessageBag();
        return redirect()->back()->with('error', $messages->first());
    }
    
    DB::beginTransaction();
    try {
        $challan = null;
        $session = Session::orderBy('id','Desc')
            ->where('active_status','1')
            ->where('created_by', '=', \Auth::user()->creatorId())
            ->first();
            
        $std_enroll = StudentEnrollments::where('enrollId', $request->student_id)->first();
        $std_reg = StudentRegistration::where('roll_no', $std_enroll->enrollId)->first();
        
        // Create challan only for inter-city transfers
        if($request->transfer_type === 'inter-city') {
            $total = 0;
            $concession = 0;
            $item = [];
            
            $challan = new Challans();
            $challan->student_id = $std_enroll->regId;
            $challan->rollno = $std_enroll->enrollId;
            $challan->class_id = $request->class_from;
            $challan->challanNo = $this->challanNo();
            $challan->challan_date = $request->transfer_date;
            $challan->challan_type = 'Transfer';
            $challan->fee_month = date('Y-m-01', strtotime($request->issue_date));
            $challan->issue_date = $request->issue_date;
            $challan->due_date = $request->due_date;
            $challan->status = 'Issued';
            $challan->session_id = $session->id;
            $challan->owned_by = $std_enroll->owned_by;
            $challan->created_by = \Auth::user()->creatorId();
            $challan->save();

            $itemIndex = 0;

            $pattern = '%transfer fee%';
            $adm_fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
            
            if(!$adm_fee_head) {
                DB::rollback();
                return redirect()->back()->with('error', 'Transfer fee head not found in system!');
            }
            
            $fee = StudentFeeStructure::where('head_id', $adm_fee_head->id)
                ->where('reg_id', $std_reg->id)
                ->orderBy('id','Desc')
                ->first();
            
            if(!$fee){
                DB::rollback();
                return redirect()->back()->with('error', 'Student Fee Structure Not Attached!');
            }
            
            $challan_head = new ChallanHead();
            $challan_head->challan_id = $challan->id;
            $challan_head->head_id = $adm_fee_head->id;
            $challan_head->price = $fee->amount;
            $challan_head->concession = round(($fee->amount / 100) * $fee->discount);
            $challan_head->save();

            $total += $fee->amount;
            $concession += round(($fee->amount / 100) * $fee->discount);
            
            $item[$itemIndex]['prod_id'] = $challan_head->id;
            $item[$itemIndex]['head'] = $adm_fee_head->id;
            $item[$itemIndex]['price'] = $fee->amount;
            $item[$itemIndex]['quantity'] = 1;
            $item[$itemIndex]['concession'] = round(($fee->amount / 100) * $fee->discount);
            $item[$itemIndex]['total'] = $total;
            $itemIndex++;

            $challan->total_amount = $total;
            $challan->save();
        }

        // Create transfer record
        $transfer = new StudentTransfer();
        $transfer->student_id = $request->student_id;
        $transfer->challan_id = $challan ? $challan->id : null;
        $transfer->transfer_date = $request->transfer_date;
        $transfer->transfer_type = $request->transfer_type;
        $transfer->branch_from = $request->branch_from;
        $transfer->class_from = $request->class_from;
        $transfer->section_from = $std_enroll->section_id;
        $transfer->branch_to = $request->branch_to;
        $transfer->class_to = $request->class_to;
        $transfer->section_to = $request->section_to;
        $transfer->reason = $request->reason;
        $transfer->session_id = $session->id;
        $transfer->owned_by = $std_enroll->owned_by;
        $transfer->created_by = \Auth::user()->creatorId();
        $transfer->save();

        // Create journal entry only for inter-city transfers (with challan)
        if($request->transfer_type === 'inter-city' && $challan) {
            $data['id'] = $challan->id;
            $data['no'] = $challan->challanNo;
            $data['date'] = $challan->challan_date;
            $data['reference'] = $std_enroll->regId;
            $data['category'] = 'Transfer';
            $data['user_id'] = $std_enroll->regId;
            $data['user_type'] = 'Student';
            $data['owned_by'] = $challan->owned_by;
            $data['created_by'] = $challan->created_by;
            $data['items'] = $item;

            $dataret = Utility::jrentry($data);
            $challan->voucher_id = $dataret;
            $challan->save();
        }
        
        DB::commit();
        
        $transferTypeText = $request->transfer_type === 'inter-city' ? 'Inter-City' : 'Intra-City';
        $message = 'Student ' . $transferTypeText . ' Transfer has been created successfully.';
        
        return redirect()->route('transferstudent.index')->with('success', $message);
        
    } catch (\Exception $e) {
        DB::rollback();
        \Log::error('Student Transfer Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'An error occurred while creating the transfer: ' . $e->getMessage());
    }
}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StudentTransfer  $studentTransfer
     * @return \Illuminate\Http\Response
     */
    public function show(StudentTransfer $studentTransfer, $id)
    {
        $transfer_order = StudentTransfer::with('student', 'classto', 'sectionto', 'branchto')->find($id);
        $fee_paid = Challans::where('student_id', $id)->where('status', 'Paid')->where('challan_type', 'Regular')->orderBy('fee_month', 'desc')->get();
        // dd($fee_paid);
        $html = view('students.student_transfer.transferorder_temp', compact('transfer_order', 'fee_paid'))->render();
        $headerHtml = view('students.student_transfer.transferorder_header')->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        $html = '<html><head>
            <style>
                @page {
                    margin-top: 100px;
                    margin-bottom: 100px;
                }
                .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
                .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
            </style>
            </head><body>
            <div class="header">' . $headerHtml . '</div>
            <div class="footer">' . $footerHtml . '</div>
            ' . $html . '
            </body></html>';
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // To load images, fonts, etc.
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        $pdfDecoded = base64_decode($base64Pdf);
        return response($pdfDecoded)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="concession_order.pdf"')
            ->header('Content-Length', strlen($pdfDecoded));

        // return response($pdfDecoded)
        //     ->header('Content-Type', 'application/pdf')
        //     ->header('Content-Disposition', 'attachment; filename="concession_order.pdf"')
        //     ->header('Content-Length', strlen($pdfDecoded));
    }


    function challanNo()
    {
        // if(\Auth::user()->type == ('company')){
            $latest = Challans::where('created_by', '=', \Auth::user()->creatorId())->orderBY('id','desc')->first();
        // }else{
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
    public function edit(StudentTransfer $studentTransfer,$id)
    {
         // if(\Auth::user()->can('edit session'))
        // {
            $studentTransfer = StudentTransfer::with('challan')->find($id);
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }
                $class_from = Classes::where('id', '=',$studentTransfer->class_from)->pluck('name', 'id');
                $class_to = Classes::where('owned_by', '=',$studentTransfer->branch_to)->pluck('name', 'id');
                $section_to = DB::table('class_sections')
                    ->join('sections', 'class_sections.section_id', '=', 'sections.id')
                    ->where('class_sections.class_id', $studentTransfer->class_to)
                    ->pluck('sections.name','sections.id');

                // $std = DB::table('student_enrollments')->join('student_registrations', 'student_enrollments.regId', '=', 'student_registrations.id')
                //             ->where('student_enrollments.class_id',$studentTransfer->class_from)->pluck('student_registrations.stdname', 'student_registrations.id');
                $std = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->where('roll_no',$studentTransfer->student_id)->pluck('stdname', 'roll_no');

            return view('students.student_transfer.edit',compact('branches','studentTransfer','class_from','class_to','section_to','std'));
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
    public function update(Request $request, StudentTransfer $studentTransfer,$id)
    {

           // if(\Auth::user()->can('create session'))
            // {
                $validator = \Validator::make(
                    $request->all(),[
                        'student_id' => 'required',
                        'transfer_date' => 'required|date',
                        'transfer_type' => 'required|string',
                        'branch_from' => 'required',
                        'class_from' => 'required',
                        'branch_to' => 'required',
                        'class_to' => 'required',
                        'section_to' => 'required',
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

                $transfer = StudentTransfer::with('challan')->find($id);
                $transfer->transfer_date = $request->transfer_date;
                $transfer->transfer_type = $request->transfer_type;
                $transfer->branch_to = $request->branch_to;
                $transfer->class_to = $request->class_to;
                $transfer->section_to = $request->section_to;
                $transfer->reason = $request->reason;
                // $transfer->owned_by = \Auth::user()->ownedId();
                // $transfer->created_by = \Auth::user()->creatorId();
                $transfer->save();

                if($request->transfer_type == 'inter branch'){
                    if(!Empty($request->challan_id)){
                        $challan = Challans::where('id', $request->challan_id)->first();
                        $challan->issue_date = $request->issue_date;
                        $challan->due_date = $request->due_date;
                        $challan->save();
                    }else{
                        $pattern = '%transfer%';
                        $adm_fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
                        $fee = StudentFeeStructure::where('head_id', $adm_fee_head->id)->where('reg_id',$std_enroll->regId)->orderBy('id','Desc')->first();
                        $challan = new Challans();
                        $challan->student_id = $request->student_id;
                        $challan->class_id = $request->class_from;
                        $challan->rollno = $std_enroll->enrollId;
                        $challan->challanNo =  $this->challanNo();
                        $challan->challan_date = $request->transfer_date;
                        $challan->challan_type = 'Transfer';
                        $challan->fee_month = date('Y-m-01',strtotime($request->issue_date));
                        $challan->issue_date = $request->issue_date;
                        $challan->due_date = $request->due_date;
                        $challan->status = 'Issued';
                        $challan->session_id = $session->id;
                        $challan->owned_by = $std_enroll->owned_by;
                        $challan->created_by = \Auth::user()->creatorId();
                        $challan->save();

                        $concessionAmount=0;
                        $itemIndex = 0;

                        $pattern = '%transfer%';
                        $adm_fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
                        $fee = StudentFeeStructure::where('head_id', $adm_fee_head->id)->where('reg_id',$request->student_id)->orderBy('id','Desc')->first();
                        $challan_head = new ChallanHead();
                        $challan_head->challan_id = $challan->id;
                        $challan_head->head_id = $adm_fee_head->id;
                        $challan_head->price = $fee ? $fee->amount : 0;
                        $challan_head->concession =  round(($fee->amount / 100) * $fee->discount);
                        $challan_head->save();

                        $total += $fee->amount;
                        $concession += round(($fee->amount / 100) * $fee->discount);
                        $item[$itemIndex]['prod_id'] = $challan_head->id;
                        $item[$itemIndex]['head'] = $adm_fee_head->id;
                        $item[$itemIndex]['price'] = $fee? $fee->amount : 0 ;
                        // $item[$itemIndex]['quantity'] = $concessiondata ? $concessiondata->concession_id : '';
                        $item[$itemIndex]['quantity'] = 1;
                        $item[$itemIndex]['concession'] = round(($fee->amount / 100) * $fee->discount);
                        $item[$itemIndex]['total'] = $total ;
                        $itemIndex++;

                        $challan->total_amount = $total;
                        $challan->save();
                        $transfer->challan_id = @$challan->id;
                        $transfer->save();

                        $data['id'] =$challan->id;
                        $data['no'] =$challan->challanNo;
                        $data['date'] =$challan->challan_date;
                        $data['reference'] =$std_enroll->regId;
                        $data['category'] = 'Transfer';
                        $data['user_id'] =$std_enroll->regId;
                        $data['user_type'] ='Student';
                        $data['owned_by'] =$challan->owned_by;
                        $data['created_by'] =$challan->created_by;
                        $data['items'] =$item;

                        $dataret  = Utility::jrentry($data);
                        $challan->voucher_id = $dataret;
                        $challan->save();
                    }
                }else{
                    if(!Empty($request->challan_id)){
                        $challan = Challans::where('id', $request->challan_id)->first();
                        if($challan->status == 'Issued'){
                            $jour = JournalEntry::where('id',$challan->voucher_id)->where('category','Transfer')->where('user_id',$std_enroll->regId)->first();
                            JournalItem::where('journal',$jour->id)->delete();
                            $jour->delete();
                            $challan->delete();
                            $transfer->challan_id = '';
                            $transfer->save();
                        }else{
                            return redirect()->route('transferstudent.index')->with('error', 'Student Transfer Challan Recipt Delete First.');
                        }
                    }
                }

                DB::commit();
                return redirect()->route('transferstudent.index')->with('success', 'Student Transfer has been updated successfully.');
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
    public function destroy(StudentTransfer $studentTransfer)
    {
        //
    }

    public function branch_class(Request $request)
    {
        $classes = Classes::where('owned_by', '=', $request->branch_id)->where('active_status',1)->get();
        return $classes;
        // $student = DB::table('student_enrollments')->join('student_registrations', 'student_enrollments.regId', '=', 'student_registrations.id')
        // ->where('student_enrollments.class_id',$request->class_id)->get();
    }
    public function class_student_head(Request $request)
    {
        $student = StudentRegistration::where('class_id',$request->class_id)->where('student_status','Enrolled')->get();
        return response()->json(['student' => $student]);
    }
    public function class_student_headwithdrawl(Request $request)
    {
        $student = StudentRegistration::where('class_id',$request->class_id)->get();
        return response()->json(['student' => $student]);
    }

    public function class_section(Request $request)
    {
        $sections = DB::table('class_sections')->
        where('class_id',$request->class_id)
        ->join('sections', 'class_sections.section_id', '=', 'sections.id')
        ->where('class_sections.class_id', $request->class_id)
        ->select('sections.id', 'sections.name')
        ->get();
        // dd($sections);
        return response()->json($sections);
    }
    public function section_student(Request $request)
    {
        if ($request->section_id == 'all') {
            $student = StudentRegistration::where('class_id', $request->class_id)->where('student_status', 'Enrolled')->get();
        } else {
            $student = DB::table('student_enrollments')->join('student_registrations', 'student_enrollments.regId', '=', 'student_registrations.id')
                ->where('student_enrollments.class_id', $request->class_id)
                ->where('student_enrollments.section_id', $request->section_id)
                ->where('student_registrations.active_status', '1')
                ->select('student_registrations.roll_no', 'student_registrations.stdname', 'student_registrations.fathername')
                ->get();
        }
        return response()->json(['student' => $student]);
    }
    public function transferapplication($id)
    {
        // dd($id);
        $studenttransfer = StudentTransfer::where('id', $id)
            ->with('student', 'branchfrom', 'challan', 'classfrom', 'classto', 'branchto', 'sectionfrom', 'sectionto')
            ->where('created_by', Auth::user()->creatorId())->first();
        $concessiondata = Concession::where('student_id', @$studenttransfer->student->id)->where('end_date', '>=', date('Y-m-d'))->orderBy('id', 'Desc')->where('status', 'Approved')->first();
        if (!$concessiondata) {
            $concessiondata = Concession::with('concession')->where('student_id', @$studenttransfer->student->id)
                ->orderBy('id', 'desc')->whereNull('end_date')->where('status', 'Approved')->first();
        }
        $classfee = StudentFeeStructure::with('feehead')->where('reg_id', @$studenttransfer->student->id)->get()->pluck('feehead.fee_head', 'head_id');
        return view('students.student_transfer.application', compact('studenttransfer', 'concessiondata', 'classfee'));
    }


    public function adminsend($id)
    {
        $studenttransfer = StudentTransfer::where('id', $id)->update(['status' => 'pending']);
        return redirect()->back()->with('success', 'Application Send to Head Office.');
    }

    public function changeStatus($id, $status)
    {
        if ($status == 'approved') {
            $studenttransfer = StudentTransfer::where('id', $id)->first();
            // dd($studenttransfer);
            StudentTransfer::where('id', $id)->update(['status' => 'approved']);
            $students = StudentRegistration::where('roll_no', $studenttransfer->student_id)
                ->update([
                    'branch' => $studenttransfer->branch_to,
                    'class_id' => $studenttransfer->class_to,
                    'owned_by' => $studenttransfer->branch_to,
                ]);
            $std_enroll = StudentEnrollments::where('enrollId', $studenttransfer->student_id)
                ->update([
                    'owned_by' => $studenttransfer->branch_to,
                    'class_id' => $studenttransfer->class_to,
                    'section_id' => $studenttransfer->section_to,
                ]);
                $his = new StudentHistory();
                $his->reg_id = $studenttransfer->student_id;
                $his->student_id = $studenttransfer->student_id;
                $his->event_type = 'transfer';
                $his->from_session_id = $studenttransfer->session_id;
                $his->from_class_id = $studenttransfer->class_from;
                $his->to_class_id = $studenttransfer->class_to;
                $his->from_section_id = $studenttransfer->section_from;
                $his->to_section_id = $studenttransfer->section_to;
                $his->from_branch_id = $studenttransfer->branch_from;
                $his->to_branch_id = $studenttransfer->branch_to;
                $his->to_session_id = $studenttransfer->session_id;
                $his->effective_date = $studenttransfer->transfer_date;
                $his->remarks = 'Student Transfer';
                $his->owned_by = $studenttransfer->owned_by;
                $his->created_by = $studenttransfer->created_by;
                $his->save();

            return redirect()->back()->with('success', 'Application Approved.');
        } elseif ($status == 'rollback') {
            $studenttransfer = StudentTransfer::where('id', $id)->update(['status' => 'rollback']);
            return redirect()->back()->with('success', 'Application Rollback.');
        } else {
            $studenttransfer = StudentTransfer::where('id', $id)->update(['status' => 'rejected']);
            return redirect()->back()->with('success', 'Application Rejected.');
        }
        return redirect()->back()->with('success', 'Application Send to Head Office.');
    }

    public function calculateBalance(Request $request)
    {
        try {
            // dd($request->all());
            $id = $request->input('student_id');
            $student = StudentRegistration::where('roll_no', $id)->first();
            $admissionChallan = Challans::where('rollno', $id)
                ->whereRaw('LOWER(challan_type) LIKE ?', [strtolower('%admission%')])
                ->first();
            if (!$admissionChallan) {
                return response()->json(['error' => 'Admission challan not found.']);
            }
            $trasf = StudentTransfer::where('id', $request->transfer_id)->first();
            if (!empty($trasf->challan_id)) {
                $transfer_challan = Challans::where('rollno', $id)->where('id', $trasf->challan_id)->first();
                if($transfer_challan){
                    $transfer_fee = $transfer_challan->total_amount - ($transfer_challan->paid_amount + $transfer_challan->concession_amount);
                }
            }

            $challanId = Challans::where('rollno', $id)->whereRaw('LOWER(challan_type) LIKE ?', [strtolower('%admission%')])->get()->pluck('id');
            $head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower('%security%')])->first();
            $challanHead = ChallanHead::with('challan')->whereIn('challan_id', $challanId)
                ->where('head_id', $head->id)->first();
            // dd($challanHead,$head->id,$head);
            if (!$challanHead) {
                return response()->json(['error' => 'No Security head attached while Registration to this student.']);
            }
            $journal = JournalEntry::whereIn('reference_id', $challanId)->get();
            // $journalItems = JournalItem::where('head', $challanHead->head_id)
            //     ->whereRaw('description REGEXP ?', ['Reciveable of Challan id : [0-9]+'])->get()->dd();
            $journalItems = JournalItem::where('head', $challanHead->head_id)
                ->whereIn('journal', $journal->pluck('id'))->get();

            $receivable = 0;
            $securityDeposit = 0;
            $netbalance = 0;
            foreach ($journalItems as $item) {
                if (preg_match('/Reciveable of Challan id : (\d+)/', $item->description, $matches)) {
                    $extractedChallanId = $matches[1];
                    if ($extractedChallanId == $challanId) {
                        if ($item->debit == 0) {
                            $securityDeposit = $item->credit;
                        } elseif ($item->credit == 0) {
                            $receivable += $item->debit;
                            $netbalance = $receivable;
                        }
                    }
                }
            }

            $arrearsTotal = Challans::where('student_id', $student->id)
                ->where('status', '!=', 'Paid')->where('challan_type', '!=', 'Transfer')
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
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['error' => 'Something Went Wrong']);
        }
    }
    public function challanheadadd(Request $request)
    {
        DB::beginTransaction();
        try {
            $studenttransfer = StudentTransfer::where('id', $request->transfer_id)->first();
            if ($studenttransfer && $studenttransfer->challan_id) {
                $challan = Challans::where('id', $studenttransfer->challan_id)->first();
                $tot_val = $challan->total_amount;
                if ($challan && count($request->fee_head) > 0) {
                    $journal = JournalEntry::where('id', '=', @$challan->voucher_id)->where('voucher_type', 'JV')->first();
                    for ($i = 0; $i < count($request->fee_head); $i++) {
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
                    $challan->total_amount = $tot_val;
                    $challan->save();
                }
            } else {
                return response()->json(['success' => 'error', 'data' => 'Challan Not Found.']);
            }
            DB::commit();
            return response()->json(['success' => 'success', 'data' => 'Challan has been updated.']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e]);
        }
    }
}

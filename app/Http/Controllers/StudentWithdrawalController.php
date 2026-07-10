<?php

namespace App\Http\Controllers;

use App\Models\ChallanHead;
use App\Models\Challans;
use App\Models\Classes;
use App\Models\JournalEntry;
use App\Models\ClassWiseFee;
use App\Models\Concession;
use App\Models\StudentHistory;
use App\Models\StudentRegistration;
use App\Models\FeeHead;
use App\Models\JournalItem;
use App\Models\ChallanSecAdjustment;
use App\Models\ChartOfAccount;
use App\Models\StudentEnrollments;
use App\Models\StudentWithdrawal;
use domPDF;
use App\Models\User;
use App\Models\Utility;
use Google\Service\Datastore\Sum;
use Illuminate\Http\Request;
use Auth;
use DB;
use Illuminate\Support\Facades\DB as FacadesDB;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentWithdrawalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request)
    {
        // dd($request->all());
        // if(\Auth::user()->can('manage session'))
        // {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = StudentWithdrawal::with('student', 'branch')->where('created_by', Auth::user()->creatorId());
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $query = StudentWithdrawal::with('student', 'branch')->where('owned_by', Auth::user()->ownedId());
        }
        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
        }
        $statusFilter = $request->status ?? 'draft';
        if (!empty($statusFilter)) {
            $query->where('status', '=', $statusFilter);
        }
        // if (!empty($request->start_date)) {
        //     $query->whereDate('withdraw_date', '>', $request->start_date);
        // }
        // if (!empty($request->end_date)) {
        //     $query->whereDate('withdraw_date', '<', $request->end_date);
        // }
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $dateFrom = $request->start_date;
            $dateTo   = $request->end_date;
        
        } else {
        
            $currentYear  = date('Y');
            $currentMonth = date('m');
        
            if ($currentMonth >= 7) {
                // Current academic session: July -> June
                $dateFrom = "$currentYear-07-01";
                $dateTo   = date('Y-06-30', strtotime('+1 year'));
            } else {
                // Previous July -> Current June
                $dateFrom = date('Y-07-01', strtotime('-1 year'));
                $dateTo   = "$currentYear-06-30";
            }
        }
        
        $query->whereBetween('withdraw_date', [$dateFrom, $dateTo]);
        $studentwithdrawal = $query->orderBy('id', 'Desc')->get();
        $status = [
            '' => 'All',
            'Draft' => 'Draft',
            'pending' => 'Pending',
            'Approved' => 'Approved',
            'Rejected' => 'Rejected',
            'Roll Back' => 'Roll Back',
        ];
        return view('students.student_withdrawal.index', compact('studentwithdrawal', 'branches', 'status', 'dateFrom', 'dateTo'));
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
            $branches->prepend('Select Branch', '');
        }
        $document_no = $this->Documentno();
        return view('students.student_withdrawal.create', compact('branches', 'document_no'));
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

        // if(\Auth::user()->can('create session'))
        // {
        $validator = \Validator::make(
            $request->all(),
            [
                'document_no' => 'required',
                // 'document_date' => 'required',
                'branch_id' => 'required',
                'class_id' => 'required',
                'student_id' => 'required',
                'withdraw_date' => 'required',
                'application_date' => 'required',
                'reason' => 'required',
                'remark' => 'required',
            ]
        );


        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        DB::beginTransaction();
        try {
            $std = StudentEnrollments::where('regId', $request->student_id)->first();

            $withdrawal = new StudentWithdrawal;
            $withdrawal->document_no = $this->Documentno();
            // $withdrawal->document_date = $request->document_date;
            $withdrawal->student_id = $std->regId;
            $withdrawal->branch_id = $request->branch_id;
            $withdrawal->class_id = $request->class_id;
            $withdrawal->withdraw_date = $request->withdraw_date;
            $withdrawal->apply_date = $request->application_date;
            $withdrawal->reason = $request->reason;
            $withdrawal->is_po = $request->is_po ? 1 : 0;
            $withdrawal->remark = $request->remark;
            $withdrawal->owned_by = $std->owned_by;
            $withdrawal->session_id = $std->session_id;
            $withdrawal->created_by = \Auth::user()->creatorId();
            $withdrawal->save();

            $reg = StudentRegistration::where('id', $std->regId)->first();
            if (!$reg) {
                $reg = StudentRegistration::where('reg_no', $std->regId)->first();
            }
            $reg->active_status = 0;
            $reg->student_status = 'withdrawal';
            $reg->save();
            $enroll = StudentEnrollments::where('enrollId', $reg->roll_no)->first();
            if ($enroll) {
                $enroll->active_status = 0;
                $enroll->save();
            }
            $his = new StudentHistory();
            $his->reg_id = $reg->id;
            $his->student_id = $reg->roll_no;
            $his->event_type = 'withdraw';
            $his->from_session_id = $reg->session_id;
            $his->from_class_id = $reg->class_id;
            $his->from_branch_id = $reg->branch;
            $his->effective_date = $request->withdraw_date;
            $his->remarks = 'Student Withdrawn';
            $his->owned_by = $reg->owned_by;
            $his->created_by = $reg->created_by;
            $his->save();

            $duplicate = Challans::where('student_id', $reg->id)
                ->where('challan_date', $request->challan_date)
                ->where('challan_type', 'Withdrawal')->first();
            if (!$duplicate) {
                $chaallan = new Challans();
                $chaallan->student_id = $reg->id;
                $chaallan->class_id = $withdrawal->class_id;
                $chaallan->rollno = $reg->roll_no;
                $chaallan->challanNo = $this->challanNo();
                $chaallan->challan_date = date('Y-m-d');
                $chaallan->fee_month = date('Y-m-01', strtotime($request->withdraw_date));
                $chaallan->challan_type = 'Withdrawal';
                $chaallan->total_amount = 0;
                $chaallan->paid_amount = 0;
                $chaallan->issue_date = $request->withdraw_date;
                $chaallan->due_date = date('Y-m-d', strtotime($request->withdraw_date . ' +7 days'));
                $chaallan->status = 'Issued';
                $chaallan->session_id = $withdrawal->session_id;
                $chaallan->owned_by = $withdrawal->owned_by;
                $chaallan->created_by = $withdrawal->created_by;
                $chaallan->save();
            }


            DB::commit();
            return redirect()->route('withdrawlstudent.index')->with('success', 'Student Withdrawal has been created successfully.');
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
     * @param  \App\Models\StudentWithdrawal  $studentWithdrawal
     * @return \Illuminate\Http\Response
     */
    public function show(StudentWithdrawal $studentWithdrawal)
    {
        //
    }
    public function Documentno()
    {
        $doc_no = StudentWithdrawal::where('created_by', Auth::user()->creatorId())->orderBy('id', 'desc')->first();
        return $doc_no ? $doc_no->document_no + 1 : 1;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StudentWithdrawal  $studentWithdrawal
     * @return \Illuminate\Http\Response
     */
    public function edit(StudentWithdrawal $studentWithdrawal, $id)
    {
        // if(\Auth::user()->can('edit session'))
        // {
        $studentWithdrawal = StudentWithdrawal::find($id);

        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('id', $studentWithdrawal->branch_id)->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        }
        $class_from = Classes::where('id', '=', $studentWithdrawal->class_id)->pluck('name', 'id');
        $section_to = DB::table('class_sections')
            ->join('sections', 'class_sections.section_id', '=', 'sections.id')
            ->where('class_sections.class_id', $studentWithdrawal->class_to)
            ->pluck('sections.name', 'sections.id');

        $std = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'id')->where('id', $studentWithdrawal->student_id)->pluck('stdname', 'id');

        return view('students.student_withdrawal.edit', compact('branches', 'studentWithdrawal', 'class_from', 'section_to', 'std'));
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
     * @param  \App\Models\StudentWithdrawal  $studentWithdrawal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StudentWithdrawal $studentWithdrawal, $id)
    {
        // if(\Auth::user()->can('create session'))
        // {
        $validator = \Validator::make(
            $request->all(),
            [
                'document_no' => 'required',
                'branch_id' => 'required',
                'class_id' => 'required',
                'student_id' => 'required',
                'withdraw_date' => 'required',
                'application_date' => 'required',
                'reason' => 'required',
                'remark' => 'required',
            ]
        );


        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        DB::beginTransaction();
        try {

            $old = StudentWithdrawal::find($id);
            $withdrawal = StudentWithdrawal::find($id);
            $withdrawal->document_no = $request->document_no;
            // $withdrawal->document_date = $request->document_date;
            $withdrawal->withdraw_date = $request->withdraw_date;
            $withdrawal->apply_date = $request->application_date;
            $withdrawal->reason = $request->reason;
            $withdrawal->is_po = $request->is_po ? 1 : 0;
            $withdrawal->remark = $request->remark;
            $withdrawal->save();
            $reg = StudentRegistration::where('id', $withdrawal->student_id)->first();
            if (!$reg) {
                $reg = StudentRegistration::where('reg_no', $withdrawal->student_id)->first();
            }

            $his = StudentHistory::where('reg_id', $reg->id)->where('student_id', $reg->roll_no)->
                where('event_type', 'withdraw')->where('effective_date', $old->withdraw_date)->first();
            $his->effective_date = $request->withdraw_date;
            $his->save();
            $challan = Challans::where('student_id', $reg->id)->where('challan_type', 'Withdrawal')->where('fee_month', date('Y-m-01', strtotime($old->withdraw_date)))->first();
            if ($challan) {
                $challan->fee_month = date('Y-m-01', strtotime($request->withdraw_date));
                $challan->challan_date = $request->withdraw_date;
                $challan->issue_date = $request->withdraw_date;
                $challan->due_date = date('Y-m-d', strtotime($request->withdraw_date . ' +7 days'));
                $challan->save();
            }
            DB::commit();
            return redirect()->route('withdrawlstudent.index')->with('success', 'Student withdrawl has been updated successfully.');
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StudentWithdrawal  $studentWithdrawal
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudentWithdrawal $studentWithdrawal)
    {
        //
    }

    public function clearance_certificate(Request $request, $id)
    {
        // Fetch the withdrawal record with relationships
        $withdrawal = StudentWithdrawal::with(['student', 'branch', 'class'])->findOrFail($id);
        $student = $withdrawal->student;
        $branch = $withdrawal->branch;
        $class = $withdrawal->class;

        // Get enrollment for admission date and roll no
        $enrollment = null;
        if ($student) {
            $enrollment = \App\Models\StudentEnrollments::where('regId', $student->id)->first();
        }

        // Get last paid challan (for last month paid amount)
        $lastPaidChallan = \App\Models\Challans::where('student_id', $student->id)
            ->where('status', 'Paid')
            ->orderByDesc('fee_month')
            ->first();
        // dd($lastPaidChallan);

        // Get security deposit info
        $securityHead = \App\Models\FeeHead::whereRaw('LOWER(fee_head) LIKE ?', ['%security%'])->first();
        $securityChallan = null;
        $securityDeposit = 0;
        $securityDepositDate = null;
        if ($securityHead) {
            $securityChallan = \App\Models\ChallanHead::leftJoin('challans', 'challan_heads.challan_id', '=', 'challans.id')
                ->where('challans.student_id', $student->id)
                ->where('challan_heads.head_id', $securityHead->id)
                ->orderBy('challans.id', 'Desc')
                ->select('challan_heads.*', 'challans.challan_date')
                ->first();
            if ($securityChallan) {
                $securityDeposit = $securityChallan->paid;
                $securityDepositDate = $securityChallan->challan_date;
            }
        }
        $arrearsTotal = \App\Models\Challans::where('rollno', $enrollment->enrollId)
            ->where('status', '!=', 'Paid')
            ->whereNotIn('challan_type', ['Transfer', 'Withdrawal'])
            ->whereRaw(
                "STR_TO_DATE(fee_month, '%Y-%m-%d') <= ?",
                [date('Y-m-d', strtotime($withdrawal->withdraw_date))]
            )
            ->select(\DB::raw('SUM(total_amount - (paid_amount + concession_amount)) AS arrears_total'))
            ->value('arrears_total');
            
        $adj = \App\Models\ChallanSecAdjustment::where('roll_no', $student->roll_no)->sum('amount');
        $securityPayable = $securityDeposit - $adj;
        $totalPayables = $securityPayable;
        $totalReceivables = $arrearsTotal - $securityPayable;
		if($totalReceivables < 0) {
            $totalReceivables = 0;
        }
		$netBalance = abs($arrearsTotal - $securityPayable);
        return view('students.student_withdrawal.certificate', [
            'withdrawal' => $withdrawal,
            'student' => $student,
            'branch' => $branch,
            'class' => $class,
            'enrollment' => $enrollment,
            'lastPaidChallan' => $lastPaidChallan,
            'securityDeposit' => $securityDeposit,
            'securityDepositDate' => $securityDepositDate,
            'arrearsTotal' => $arrearsTotal,
            'securityPayable' => $securityPayable,
            'totalPayables' => $totalPayables,
            'totalReceivables' => $totalReceivables,
            'netBalance' => $netBalance,
        ]);
    }


    public function withdrawlapplication($id)
    {

        $studentwithdrawal = StudentWithdrawal::where('id', $id)
            ->with('student', 'branch', 'student.class')->where('created_by', Auth::user()->creatorId())->first();
        $enrollment = StudentEnrollments::where('regId', $studentwithdrawal->student_id)->first();
        if (!$enrollment) {
            return redirect()->back()->with('error', 'Enrollment record not found for this student.');
        }
        $studentchallan = Challans::where('rollno', $enrollment->enrollId)
            ->with('student', 'receipts', 'vouchers')->where('status', '!=', 'Paid')
            ->where('created_by', Auth::user()->creatorId())->first();

        $PrevChallan = Challans::where('rollno', $enrollment->enrollId)
            ->where('status', '!=', 'Paid')
            ->whereNotIn('challan_type', ['Transfer', 'Withdrawal'])
            ->whereRaw(
                "STR_TO_DATE(fee_month, '%Y-%m-%d') <= ?",
                [date('Y-m-d', strtotime($studentwithdrawal->withdraw_date))]
            )
            ->get();
        // dd($PrevChallan,$studentwithdrawal->withdraw_date);
        $head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower('%security%')])->first();
        $challan = ChallanHead::leftjoin('challans', 'challan_heads.challan_id', '=', 'challans.id')
            ->where('challans.rollno', $enrollment->enrollId)
            ->where('challan_heads.head_id', $head->id)
            ->orderBy('challans.id', 'Desc')
            ->first();
        // dd($challan);
        $payable_amount = 0;
        if ($challan) {
            $paymentamount = $challan->price - ($challan->paid + $challan->concession);
            // dd($paymentamount);
            if ($paymentamount == 0) {
                $payable_amount = $challan->paid;
            }
        }
        $all_accounts = ChartOfAccount::where('created_by', Auth::user()->creatorId())->get();

        $adj_entry = ChallanSecAdjustment::with('challan')->where('roll_no', $enrollment->enrollId)->get();
        $payable = $payable_amount - $adj_entry->sum('amount');
        // dd($payable,$adj_entry->sum('amount'),$payables);
        $withdrawal_challan = Challans::where('student_id', $studentwithdrawal->student_id)->where('challan_type', 'Withdrawal')
            ->whereDate('fee_month', date('Y-m-d', strtotime($studentwithdrawal->withdraw_date)))->orderBy('id', 'desc')->first();
        return view('students.student_withdrawal.application', compact('studentwithdrawal', 'PrevChallan', 'payable', 'adj_entry', 'all_accounts', 'withdrawal_challan'));
    }
    public function calculateBalance(Request $request)
    {
        $id = $request->input('student_id');
        $student = StudentRegistration::where('id', $id)->first();
        // dd($student);
        if (!$student) {
            $student = StudentRegistration::where('reg_no', $id)->first();
        }
        $studentwithdrawal = StudentWithdrawal::where('student_id', $id)->where('created_by', Auth::user()->creatorId())->orderby('id', 'desc')->first();
        $admissionChallan = Challans::where('student_id', $id)
            ->whereRaw('LOWER(challan_type) LIKE ?', [strtolower('%admission%')])
            ->first();
        $admissionError = null;

        if (!$admissionChallan) {
            $admissionError = 'No Adm challan found.';
        }
        // $trasf = StudentWithdrawal::where('id', $request->transfer_id)->first();
        // if (!empty($trasf->challan_id)) {
        //     $transfer_challan = Challans::where('student_id', $id)->where('id', $trasf->challan_id)->first();
        //     if($transfer_challan){
        //         $transfer_fee = $transfer_challan->total_amount - ($transfer_challan->paid_amount + $transfer_challan->concession_amount);
        //     }
        // }

        $payable = 0;
        $receivable = 0;
        $securityDeposit = 0;
        $netbalance = 0;
        $receivable = 0;

        // $challanId = Challans::where('rollno', $id)->whereRaw('LOWER(challan_type) LIKE ?', [strtolower('%admission%')])->get()->pluck('id');
        $head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower('%security%')])->first();

        $challan = ChallanHead::leftjoin('challans', 'challan_heads.challan_id', '=', 'challans.id')
            ->where('challans.student_id', $id)
            ->where('challan_heads.head_id', $head->id)
            ->where('challans.status', 'paid')
            ->orderBy('challans.id', 'Desc')->first();
        // dd($challan,$id);
        if ($challan) {
            $securityDeposit = $challan->paid;
            // $securityDeposit = $challan->price - $challan->concession;
            $payable = $challan->paid;
        }

        // $challanHead = ChallanHead::with('challan')->whereIn('challan_id', $challanId)->where('head_id', $head->id)->first();
        // if ($challanHead && !is_null($challanHead)) {
        //     // dd($challanHead,$head->id,$head);
        //     // return response()->json(['error' => 'No Security head attached while Registration to this student.']);

        //         $journal = JournalEntry::whereIn('reference_id', $challanId)->get();
        //         // $journalItems = JournalItem::where('head', $challanHead->head_id)
        //         //     ->whereRaw('description REGEXP ?', ['Reciveable of Challan id : [0-9]+'])->get()->dd();
        //         $journalItems = JournalItem::where('head', $challanHead->head_id)
        //             ->whereIn('journal', $journal->pluck('id'))->get();
        //     foreach ($journalItems as $item) {
        //         if (preg_match('/Reciveable of Challan no : (\d+)/', $item->description, $matches)) {
        //             $extractedChallanId = $matches[1];

        //             if ($extractedChallanId == $challanId) {
        //                 if ($item->debit == 0) {
        //                     $securityDeposit = $item->credit;
        //                 } elseif ($item->credit != 0) {
        //                     $receivable += $item->debit;
        //                     $netbalance = $receivable;
        //                 }
        //             }
        //         }
        //     }
        // }
        // $arrearsTotal = Challans::where('student_id', $student->id)
        //     ->where('status', '!=', 'Paid')->where('challan_type', '!=', 'Transfer')->wheredate('fee_month', '<=', date('Y-m-01'))
        //     ->select(\DB::raw('SUM(total_amount - (paid_amount + concession_amount)) AS arrears_total'))->value('arrears_total');
        // dd($student);
        $arrearsTotal = Challans::where('rollno', $student->roll_no)
            ->where('status', '!=', 'Paid')
            ->whereNotIn('challan_type', ['Transfer', 'Withdrawal'])
            ->wheredate('fee_month', '<=', date('Y-m-d', strtotime($studentwithdrawal->withdraw_date)))
            ->select(\DB::raw('SUM(total_amount - (paid_amount + concession_amount)) AS arrears_total'))->value('arrears_total');

        $adj = ChallanSecAdjustment::where('roll_no', $student->roll_no)->sum('amount');
        return response()->json([
            'error' => false,
            'admission_error' => $admissionError,
            'actual_fee' => $arrearsTotal ?? 0,
            'security_deposit' => $securityDeposit ?? 0,
            'security_payable' => ($payable - $adj) ?? 0,
            'other_fee' => 0,
            'refund' => 0,
            'notice_fee' => 0,
            'other_deduction' => 0,
            'total_payables' => ($payable - $adj) ?? 0,
            'total_receivables' => $arrearsTotal ?? 0,
            'net_balance' => abs(($arrearsTotal ?? 0) - (($payable - $adj) ?? 0)),
        ]);
    }

    public function submit_adjustment(Request $request)
    {
        if ($request->used == 0) {
            redirect()->back()->with('error', 'Please enter a valid adjustment amount.');
        }
        DB::beginTransaction();
        try {
            $filtered = [];

            $item = [];
            $i = 0;
            $total = 0;
            foreach ($request->oldramount as $index => $amount) {
                if (!empty($amount) && $amount > 0) {
                    $filtered[] = [
                        'head_id' => $request->oldhead_id[$index],
                        'amount' => $amount
                    ];
                    $item[$i]['head'] = $request->oldhead_id[$index];
                    $item[$i]['price'] = $amount;
                    $item[$i]['quantity'] = 1;
                    $item[$i]['concession'] = 0;
                    $item[$i]['total'] = $amount;
                    $i++;
                    $total += $amount;
                }
            }

            if ($total <= $request->total_available) {
                foreach ($item as $it) {
                    $challanhead = ChallanHead::with('challan')->where('challan_id', $request->challan_id)->where('head_id', $it['head'])->first();
                    $challanhead->paid = $challanhead->paid + $it['total'];
                    $challanhead->save();
                    $cha = Challans::where('id', $request->challan_id)->first();
                    $cha->paid_amount = $cha->paid_amount + $it['total'];
                    $cha->save();
                }
            }
            $admissionChallan = Challans::where('id', $request->challan_id)->first();
            $adj_challan = ChallanSecAdjustment::create([
                'roll_no' => $admissionChallan->rollno,
                'challan_id' => $admissionChallan->id,
                'amount' => $total,
                'date' => date('Y-m-d'),
                'owned_by' => $admissionChallan->owned_by,
                'created_by' => $admissionChallan->created_by,
            ]);

            $data['id'] = $admissionChallan->id;
            $data['no'] = $admissionChallan->challanNo;
            $data['date'] = date('Y-m-d');
            // $data['adj_entry'] = $recipts->id;
            $data['reference'] = $admissionChallan->student_id;
            $data['description'] = 'Adjusment Voucher for Challan no ' . $admissionChallan->challanNo;
            $data['user_id'] = $admissionChallan->student_id;
            $data['total'] = $total;
            $data['user_type'] = 'Student';
            $data['category'] = 'Adjusment';
            $data['owned_by'] = $admissionChallan->owned_by;
            $data['created_by'] = $admissionChallan->created_by;
            $data['items'] = $item;

            $dataret = Utility::adj_voucher($data);
            // $adj_challan->amount = $adj_amount;
            $adj_challan->voucher_id = $dataret;
            $adj_challan->save();
            // $challanhead = ChallanHead::with('challan')->where('challan_id', $admissionChallan->id)
            // ->select('id', 'head_id','challan_id', 'price', 'paid', 'concession', \DB::raw('(price - (paid + concession)) AS arrears'))
            // ->having('arrears', '>', 0)->orderby('id','Asc')->get();

            // dd($admissionChallan,$challanhead,$request->all(),$filtered,$item);

            // if($request->adjAmount > 0){
            //     $v = $request->adjAmount;
            //     $i = 0;
            //     $item = [];
            //     foreach($challanhead as $challan){
            //         $ar =$v - $challan->arrears;
            //         if($ar <= 0){
            //             $item[$i]['head'] = $challan->head_id;
            //             $item[$i]['price'] = $v;
            //             $item[$i]['quantity'] = 1;
            //             $item[$i]['concession'] = 0;
            //             $item[$i]['total'] = $v;

            //             $i++;
            //             break;

            //         }else{
            //             $v =$v - $challan->arrears;
            //             $item[$i]['head'] = $challan->head_id;
            //             $item[$i]['price'] = $challan->arrears;
            //             $item[$i]['quantity'] = 1;
            //             $item[$i]['concession'] = 0;
            //             $item[$i]['total'] = $challan->arrears;
            //             $i++;
            //         }

            //     }

            //     $adj_amount = 0;
            //     $adj_challan = ChallanSecAdjustment::create([
            //         'roll_no' => $admissionChallan->rollno,
            //         'challan_id' => $admissionChallan->id,
            //         'amount' => '0',
            //         'date' => date('Y-m-d'),
            //         'owned_by' => $admissionChallan->owned_by,
            //         'created_by' => $admissionChallan->created_by,
            //     ]);

            //     foreach($item as $it){
            //         $adj_amount += $it['price'];
            //         $challanhead = ChallanHead::where('head_id', $it['head'])->where('challan_id' , $admissionChallan->id)->first();
            //         $challanhead->paid = $challanhead->paid + $it['price'];
            //         $challanhead->save();
            //     }
            //     $admissionChallan->paid_amount = $admissionChallan->paid_amount + $adj_amount;
            //     $admissionChallan->save();

            //         $data['id'] = $admissionChallan->id;
            //         $data['no'] = $admissionChallan->challanNo;
            //         $data['date'] = date('Y-m-d');
            //         // $data['adj_entry'] = $recipts->id;
            //         $data['reference'] = $admissionChallan->student_id;
            //         $data['description'] = 'Adjusment Voucher';
            //         $data['user_id'] = $admissionChallan->student_id;
            //         $data['total'] = $adj_amount;
            //         $data['user_type'] = 'Student';
            //         $data['category'] = 'Adjusment';
            //         $data['owned_by'] = $admissionChallan->owned_by;
            //         $data['created_by'] = $admissionChallan->created_by;
            //         $data['items'] = $item;

            //         $dataret = Utility::adj_voucher($data);
            //         $adj_challan->amount = $adj_amount;
            //         $adj_challan->voucher_id = $dataret;
            //         $adj_challan->save();
            //         //reg 
            //         // dd($admissionChallan);
            //         $reg = StudentRegistration::where('id', $admissionChallan->student_id)->first();
            //         // $reg->student_status = 'withdrawl';
            //         $reg->save();
            //         DB::commit();
            // }
            DB::commit();
            return redirect()->back()->with('success', 'Adjustment has been created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return redirect()->back()->with('error', $e);
        }
    }

    public function delete_adjustment(Request $request)
    {
        DB::beginTransaction();
        try {
            $adj_challan = ChallanSecAdjustment::where('id', $request->id)->first();
            $v = 0;
            $i = 0;
            $item = [];
            $challan = Challans::where('id', $adj_challan->challan_id)->first();
            $voucher = JournalEntry::where('id', $adj_challan->voucher_id)->first();
            $items = JournalItem::where('journal', $adj_challan->voucher_id)->get();
            foreach ($items as $it) {
                $challanhead = ChallanHead::with('challan')->where('challan_id', $challan->id)->where('head_id', $it->head)->first();
                if ($it->credit != 0) {
                    $challanhead->paid = $challanhead->paid - $it->credit;
                    $challanhead->save();
                    $v += $it->credit;
                    $i++;
                }
            }
            $challan->paid_amount = $challan->paid_amount - $v;
            $challan->save();
            $items = JournalItem::where('journal', $adj_challan->voucher_id)->delete();
            $voucher->delete();
            $adj_challan->delete();

            // $challan = Challans::where('challanNo', $request->challanNo)->first();
            // $adj_challan = ChallanSecAdjustment::where('challan_id',$challan->id)->get();
            // // $challanhead = ChallanHead::with('challan')->where('challan_id', $admissionChallan->id)
            // // ->select('id', 'head_id','challan_id', 'price', 'paid', 'concession', \DB::raw('(price - (paid + concession)) AS arrears'))
            // // ->having('arrears', '>', 0)->orderby('id','Asc')->get();
            // $v = 0;
            // $i = 0;
            // $item = [];
            // foreach($adj_challan as $cha){
            //     $voucher = JournalEntry::where('id', $cha->voucher_id)->first();
            //     $items = JournalItem::where('journal',$voucher->id)->get();
            //     // $ar =$v - $challan->arrears;
            //     foreach($items as $head_item){
            //         if($head_item->credit != 0){
            //             $v += $head_item->credit;
            //             $item[$i]['head'] = $head_item->head;
            //             $item[$i]['total'] = $head_item->credit;
            //             $i++;
            //         }
            //     }
            //     $voucher->delete();
            //     $items = JournalItem::where('journal',$voucher->id)->delete();
            // }

            // foreach($item as $it){
            //     $challanhead = ChallanHead::where('head_id', $it['head'])->where('challan_id' , $challan->id)->first();
            //     $challanhead->paid = $challanhead->paid - $it['total'];
            //     $challanhead->save();
            // }
            // $challan->paid_amount = $challan->paid_amount - $v;
            // $challan->save();
            // $adj_challan = ChallanSecAdjustment::where('challan_id',$challan->id)->delete();

            //     // $data['id'] = $admissionChallan->id;
            //     // $data['no'] = $admissionChallan->challanNo;
            //     // $data['date'] = date('Y-m-d');
            //     // // $data['adj_entry'] = $recipts->id;
            //     // $data['reference'] = $admissionChallan->student_id;
            //     // $data['description'] = 'Adjusment Voucher';
            //     // $data['user_id'] = $admissionChallan->student_id;
            //     // $data['total'] = $adj_amount;
            //     // $data['user_type'] = 'Student';
            //     // $data['category'] = 'Adjusment';
            //     // $data['owned_by'] = $admissionChallan->owned_by;
            //     // $data['created_by'] = $admissionChallan->created_by;
            //     // $data['items'] = $item;

            //     // $dataret = Utility::adj_voucher($data);
            //     // $adj_challan->amount = $adj_amount;
            //     // $adj_challan->voucher_id = $dataret;
            //     // $adj_challan->save();
            DB::commit();

            return response()->json([
                'success' => 'success',
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return redirect()->back()->with('error', $e);
        }
    }
    public function saveBasics(Request $request, $id)
    {
        try {
            $studentwithdrawal = StudentWithdrawal::where('id', $id)->first();
            if (!$studentwithdrawal) {
                return response()->json(['error' => 'Withdrawal record not found.'], 404);
            }
            $studentwithdrawal->remark = $request->remarks;
            $studentwithdrawal->ho_remarks = $request->ho_remarks;
            $studentwithdrawal->save();

            return response()->json(['success' => 'Data saved successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function withdrawlapplicationstore(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // dd($request->all());
            $studentwithdrawal = StudentWithdrawal::where('id', $id)->first();
            if ($request->net_balance != 0 && $request->challan_date != null) {
                //payable challan 
            }
            // $studentwithdrawal->withdraw_date = $request->withdrawl_date;
            // $studentwithdrawal->reason = $request->reason;
            // $studentwithdrawal->document_no = $request->document_number;
            // $studentwithdrawal->save();
            //history
            $hoSnapshot = [
                'actual_fee' => $request->actual_fee,
                'security_deposit' => $request->security_deposit,
                'security_payable' => $request->security_payable,
                'other_fee' => $request->other_fee,
                'other_account' => $request->other_account,
                'refund' => $request->refund,
                'notice_fee' => $request->notice_fee,
                'other_deduction' => $request->other_deduction,
                'total_payables' => $request->total_payables,
                'total_receivables' => $request->total_receivables,
                'net_balance' => $request->net_balance,
            ];
            $studentwithdrawal->ho_snapshot = $hoSnapshot;
            $studentwithdrawal->expected_readmission_date = $request->expected_readmission_date;
            $studentwithdrawal->remark = $request->remarks;
            $studentwithdrawal->ho_remarks = $request->ho_remarks;
            $studentwithdrawal->status = 'approved';
            $studentwithdrawal->save();
            $reg = StudentRegistration::where('id', $studentwithdrawal->student_id)->first();
            if (!$reg) {
                $reg = StudentRegistration::where('reg_no', $studentwithdrawal->student_id)->first();
            }
            $reg->student_status = 'withdrawl';
            $reg->save();

            $duplicate = Challans::where('student_id', $reg->id)
                ->where('challan_date', $request->challan_date)
                ->where('challan_type', 'Withdrawal')->first();
            if (!$duplicate) {
                $chaallan = new Challans();
                $chaallan->student_id = $reg->id;
                $chaallan->class_id = $studentwithdrawal->class_id;
                $chaallan->rollno = $reg->roll_no;
                $chaallan->challanNo = $this->challanNo();
                $chaallan->challan_date = $request->challan_date;
                $chaallan->fee_month = date('Y-m-01', strtotime($request->challan_date));
                $chaallan->challan_type = 'Withdrawal';
                $chaallan->total_amount = 0;
                $chaallan->paid_amount = 0;
                $chaallan->issue_date = $request->challan_date;
                $chaallan->due_date = $request->due_date;
                $chaallan->status = 'Issued';
                $chaallan->session_id = $studentwithdrawal->session_id;
                $chaallan->owned_by = $studentwithdrawal->owned_by;
                $chaallan->created_by = $studentwithdrawal->created_by;
                $chaallan->save();
            }
            // $his = new StudentHistory();
            // $his->reg_id = $studentwithdrawal->student_id;
            // $his->student_id = $studentwithdrawal->student_id;
            // $his->event_type = 'withdraw';
            // $his->from_session_id = $studentwithdrawal->session_id;
            // $his->from_class_id = $studentwithdrawal->class_id;
            // $his->from_branch_id = $studentwithdrawal->branch_id;
            // $his->effective_date = $studentwithdrawal->withdraw_date;
            // $his->remarks = 'Student Withdrawn';
            // $his->owned_by = $studentwithdrawal->owned_by;
            // $his->created_by = $studentwithdrawal->created_by;
            // $his->save();
            DB::commit();
            return redirect()->route('withdrawlstudent.index')->with('success', 'Student Withdrawal has been created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return redirect()->back()->with('error', $e);
        }
    }

    public function certificatePdf($id)
    {
        $withdrawal = StudentWithdrawal::with(['student', 'branch', 'class'])->findOrFail($id);
        $student = $withdrawal->student;
        $branch = $withdrawal->branch;
        $class = $withdrawal->class;

        $enrollment = null;
        if ($student) {
            $enrollment = \App\Models\StudentEnrollments::where('regId', $student->id)->first();
        }

        $lastPaidChallan = \App\Models\Challans::where('student_id', $student->id)
            ->where('status', 'Paid')
            ->orderByDesc('fee_month')
            ->first();

        $securityHead = \App\Models\FeeHead::whereRaw('LOWER(fee_head) LIKE ?', ['%security%'])->first();
        $securityChallan = null;
        $securityDeposit = 0;
        $securityDepositDate = null;
        if ($securityHead) {
            $securityChallan = \App\Models\ChallanHead::leftJoin('challans', 'challan_heads.challan_id', '=', 'challans.id')
                ->where('challans.student_id', $student->id)
                ->where('challan_heads.head_id', $securityHead->id)
                ->orderBy('challans.id', 'Desc')
                ->select('challan_heads.*', 'challans.challan_date')
                ->first();
            if ($securityChallan) {
                $securityDeposit = $securityChallan->paid;
                // $securityDeposit = $securityChallan->price - $securityChallan->concession;
                $securityDepositDate = $securityChallan->challan_date;
            }
        }
        $arrearsTotal = \App\Models\Challans::where('student_id', $student->id)
            ->where('status', '!=', 'Paid')
            ->where('challan_type', '!=', 'Transfer')
            ->wheredate('fee_month', '<=', date('Y-m-01'))
            ->select(\DB::raw('SUM(total_amount - (paid_amount + concession_amount)) AS arrears_total'))
            ->value('arrears_total');
        $adj = \App\Models\ChallanSecAdjustment::where('roll_no', $student->roll_no)->sum('amount');
        $securityPayable = $securityDeposit - $adj;
        $totalPayables = $securityPayable;
        $totalReceivables = $arrearsTotal - $securityPayable;
        $netBalance = $arrearsTotal - $securityPayable;
        $pdf = Pdf::loadView('students.student_withdrawal.printCC', [
            'withdrawal' => $withdrawal,
            'student' => $student,
            'branch' => $branch,
            'class' => $class,
            'enrollment' => $enrollment,
            'lastPaidChallan' => $lastPaidChallan,
            'securityDeposit' => $securityDeposit,
            'securityDepositDate' => $securityDepositDate,
            'arrearsTotal' => $arrearsTotal,
            'securityPayable' => $securityPayable,
            'totalPayables' => $totalPayables,
            'totalReceivables' => $totalReceivables,
            'netBalance' => $netBalance,
        ]);

        return $pdf->stream('clearance_certificate.pdf');
    }

    public function certificatePrint($id)
    {
        $withdrawal = StudentWithdrawal::with(['student', 'branch', 'class'])->findOrFail($id);
        $student = $withdrawal->student;
        $branch = $withdrawal->branch;
        $class = $withdrawal->class;

        $enrollment = null;
        if ($student) {
            $enrollment = \App\Models\StudentEnrollments::where('regId', $student->id)->first();
        }

        $admissionChallanNo = '-';
        if ($student) {
            $admissionChallan = \App\Models\Challans::where('student_id', $student->id)
                ->where('challan_type', 'Admission')
                ->orderBy('id', 'desc')
                ->first();
            $admissionChallanNo = $admissionChallan ? $admissionChallan->challanNo : '-';
        }

        $lastPaidChallan = \App\Models\Challans::where('student_id', $student->id)
            ->where('status', 'Paid')
            ->orderByDesc('fee_month')
            ->first();

        $securityHead = \App\Models\FeeHead::whereRaw('LOWER(fee_head) LIKE ?', ['%security%'])->first();
        $securityChallan = null;
        $securityDeposit = 0;
        $securityDepositDate = null;
        if ($securityHead) {
            $securityChallan = \App\Models\ChallanHead::leftJoin('challans', 'challan_heads.challan_id', '=', 'challans.id')
                ->where('challans.student_id', $student->id)
                ->where('challan_heads.head_id', $securityHead->id)
                ->orderBy('challans.id', 'Desc')
                ->select('challan_heads.*', 'challans.challan_date')
                ->first();
            if ($securityChallan) {
                // $securityDeposit = $securityChallan->price - $securityChallan->concession;
                $securityDeposit = $securityChallan->paid;
                $securityDepositDate = $securityChallan->challan_date;
            }
        }
        $arrearsTotal = \App\Models\Challans::where('student_id', $student->id)
            ->where('status', '!=', 'Paid')
            ->where('challan_type', '!=', 'Transfer')
            ->wheredate('fee_month', '<=', date('Y-m-01'))
            ->select(\DB::raw('SUM(total_amount - (paid_amount + concession_amount)) AS arrears_total'))
            ->value('arrears_total');
        $adj = \App\Models\ChallanSecAdjustment::where('roll_no', $student->roll_no)->sum('amount');
        $securityPayable = $securityDeposit - $adj;
        $totalPayables = $securityPayable;
        $totalReceivables = $arrearsTotal - $securityPayable;
        $netBalance = $arrearsTotal - $securityPayable;

        $pdf = Pdf::loadView('students.student_withdrawal.printWithdrawalCertificate', [
            'withdrawal' => $withdrawal,
            'student' => $student,
            'branch' => $branch,
            'class' => $class,
            'enrollment' => $enrollment,
            'lastPaidChallan' => $lastPaidChallan,
            'securityDeposit' => $securityDeposit,
            'securityDepositDate' => $securityDepositDate,
            'arrearsTotal' => $arrearsTotal,
            'securityPayable' => $securityPayable,
            'totalPayables' => $totalPayables,
            'totalReceivables' => $totalReceivables,
            'netBalance' => $netBalance,
        ]);

        return $pdf->stream('withdrawal_certificate.pdf');
    }

    public function settlementCertificate($id)
    {
        $withdrawal = StudentWithdrawal::with(['student', 'branch', 'class'])->findOrFail($id);
        $student = $withdrawal->student;
        $branch = $withdrawal->branch;
        $class = $withdrawal->class;

        $admissionChallanNo = 'N/A';
        $admissionBranch = null;
        $enrollment = null;
        if ($student) {
            $enrollment = \App\Models\StudentEnrollments::where('regId', $student->id)->first();
            $admissionBranch = $enrollment && $enrollment->branch ? $enrollment->branch->name : null;

            $admissionChallan = \App\Models\Challans::where('student_id', $student->id)
                ->where('challan_type', 'Admission')
                ->orderBy('id', 'desc')
                ->first();
            $admissionChallanNo = $admissionChallan ? $admissionChallan->challanNo : 'N/A';
        }

        $lastPaidChallan = \App\Models\Challans::where('student_id', $student->id)
            ->where('status', 'Paid')
            ->orderByDesc('fee_month')
            ->first();

        $securityHead = \App\Models\FeeHead::whereRaw('LOWER(fee_head) LIKE ?', ['%security%'])->first();
        $securityDeposit = 0;
        $securityDepositDate = null;
        $securityChallanNo = 'N/A';
        if ($securityHead) {
            // Sum all credit amounts for this security fee head from paid journal items
            $securityJournalItems = \App\Models\JournalItem::where('head', $securityHead->id)
                ->where('user_type', 'Student')
                ->where('user_id', $student->id)
                ->where('types', 'Challan Payment')
                ->where('credit', '>', 0)
                ->get();
            // dd($securityJournalItems);
            $securityDeposit = $securityJournalItems->sum('credit');

            // Collect all unique receipt dates
            $receiptDates = [];
            foreach ($securityJournalItems as $item) {
                if ($item->receipt_id) {
                    $receipt = \App\Models\StudentReceipt::find($item->receipt_id);
                    if ($receipt && $receipt->recipt_date) {
                        $receiptDates[] = \Carbon\Carbon::parse($receipt->recipt_date)->format('d M Y');
                    }
                }
            }
            $receiptDates = array_unique($receiptDates);
            $securityDepositDate = !empty($receiptDates) ? implode(', ', $receiptDates) : null;

            // Get the challan number where security was charged
            $securityChallan = \App\Models\ChallanHead::leftJoin('challans', 'challan_heads.challan_id', '=', 'challans.id')
                ->where('challans.student_id', $student->id)
                ->where('challan_heads.head_id', $securityHead->id)
                ->orderBy('challans.id', 'Desc')
                ->select('challans.challanNo')
                ->first();
            $securityChallanNo = $securityChallan->challanNo ?? 'N/A';
        }
        $arrearsTotal = Challans::where('rollno', $student->roll_no)
            ->where('status', '!=', 'Paid')
            ->whereNotIn('challan_type', ['Transfer', 'Withdrawal'])
            ->wheredate('fee_month', '<=', date('Y-m-d', strtotime($withdrawal->withdraw_date)))
            ->select(\DB::raw('SUM(total_amount - (paid_amount + concession_amount)) AS arrears_total'))->value('arrears_total');
        $adj = \App\Models\ChallanSecAdjustment::where('roll_no', $student->roll_no)->sum('amount');
        $securityPayable = $securityDeposit - $adj;
        $totalPayables = $securityPayable;
        $totalReceivables = $arrearsTotal - $securityPayable;
        $netBalance = $arrearsTotal - $securityPayable;
        // dd($securityDeposit,$adj, $securityPayable, $arrearsTotal );
        $lastReceiptAmount = 0;
        $lastReceiptDate = null;
        $otherDeduction = 0;

        // Excess refund: paid challans with fee_month after withdrawal date
        $excessRefund = 0;
        if ($withdrawal->withdraw_date) {
            $excessRefund = \App\Models\Challans::where('student_id', $student->id)
                ->where('status', 'Paid')
                ->whereNotIn('challan_type', ['Transfer', 'Withdrawal'])
                ->whereDate('fee_month', '>', $withdrawal->withdraw_date)
                ->sum('paid_amount');
        }

        if ($lastPaidChallan) {
            $lastReceipt = \App\Models\StudentReceipt::where('challan_id', $lastPaidChallan->id)
                ->orderBy('id', 'desc')
                ->first();
            $lastReceiptAmount = $lastReceipt ? $lastReceipt->recipt_amount : 0;
            $lastReceiptDate = $lastReceipt ? $lastReceipt->recipt_date : null;
        }

        $pdf = Pdf::loadView('students.student_withdrawal.clearanceCertificatePrint', [
            'withdrawal' => $withdrawal,
            'student' => $student,
            'branch' => $branch,
            'class' => $class,
            'enrollment' => $enrollment,
            'lastPaidChallan' => $lastPaidChallan,
            'securityDeposit' => $securityDeposit,
            'securityChallanNo' => $securityChallanNo,
            'securityDepositDate' => $securityDepositDate,
            'arrearsTotal' => $arrearsTotal,
            'securityPayable' => $securityPayable,
            'totalPayables' => $totalPayables,
            'totalReceivables' => $totalReceivables,
            'netBalance' => $netBalance,
            'lastReceiptAmount' => $lastReceiptAmount,
            'lastReceiptDate' => $lastReceiptDate,
            'admissionChallanNo' => $admissionChallanNo,
            'admissionBranch' => $admissionBranch,
            'otherDeduction' => $otherDeduction,
            'excessRefund' => $excessRefund,
        ]);

        return $pdf->stream('settlement_certificate.pdf');
    }

    public function fwdtoho(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $studentwithdrawal = StudentWithdrawal::where('id', $id)->first();
            $branchSnapshot = [
                'actual_fee' => $request->actual_fee,
                'security_deposit' => $request->security_deposit,
                'security_payable' => $request->security_payable,
                'other_fee' => $request->other_fee,
                'other_account' => $request->other_account,
                'refund' => $request->refund,
                'notice_fee' => $request->notice_fee,
                'other_deduction' => $request->other_deduction,
                'total_payables' => $request->total_payables,
                'total_receivables' => $request->total_receivables,
                'net_balance' => $request->net_balance,
            ];
            $studentwithdrawal->branch_snapshot = $branchSnapshot;
            $studentwithdrawal->remark = $request->remarks;
            $studentwithdrawal->fwd_to_ho = 1;
            $studentwithdrawal->status = 'pending';
            $studentwithdrawal->save();
            DB::commit();
            return redirect()->route('withdrawlstudent.index')->with('success', 'Student Withdrawal has been forwarded to HO successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function challanNo()
    {
        $lastChallan = Challans::orderBy('id', 'desc')->first();
        if ($lastChallan) {
            $lastChallanNo = $lastChallan->challanNo;
            $challanNo = $lastChallanNo + 1;
        } else {
            $challanNo = 1;
        }
        return $challanNo;
    }

    public function studentchallandetail(Request $request)
    {
        $lastChallan = Challans::with('unpaidHeads')->where('id', $request->id)->first();
        if ($lastChallan) {
            $head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower('%security%')])->first();
            $challan = ChallanHead::leftjoin('challans', 'challan_heads.challan_id', '=', 'challans.id')
                ->where('challans.rollno', $lastChallan->rollno)
                ->where('challan_heads.head_id', $head->id)
                ->orderBy('challans.id', 'Desc')
                ->first();

            $payable = 0;
            if ($challan) {
                $payyment = $challan->price - ($challan->paid + $challan->concession);
                if ($payyment == 0) {
                    $payable = $challan->paid;
                }
            }

            $adj = ChallanSecAdjustment::where('roll_no', $lastChallan->rollno)->sum('amount');
            $payable = $payable - $adj;
            // dd($adj,$payable);
            $totalAvailable = $payable;
            // dd($payable);
            return response()->json([
                'html' => view('students.student_withdrawal.challan_detail', compact('lastChallan', 'totalAvailable', 'payable'))->render()
            ]);
        } else {

        }
    }
    public function studentadjustdetail(Request $request)
    {
        $adjustment = ChallanSecAdjustment::where('id', $request->id)->first();
        $challan = Challans::where('id', $adjustment->challan_id)->first();
        $entry_item = JournalItem::where('journal', $adjustment->voucher_id)->where('credit', '!=', 0)->get();
        $heads = [];
        $i = 0;
        foreach ($entry_item as $entry) {
            $head_id = $entry->head;
            $challan_head = ChallanHead::with('feehead')->where('head_id', $head_id)->where('challan_id', $challan->id)->first();
            $heads[$i]['head_id'] = $head_id;
            $heads[$i]['head_name'] = $challan_head->feehead->fee_head;
            $heads[$i]['amount'] = $entry->credit;
            $heads[$i]['description'] = $entry->description;
            $i++;
        }
        return response()->json([
            'html' => view('students.student_withdrawal.challan_detail_adjustment', compact('adjustment', 'challan', 'heads'))->render()
        ]);

    }
}

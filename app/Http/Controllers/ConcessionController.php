<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Concession;
use App\Models\ConcessionPolicy;
use App\Models\ConcessionPolicyHead;
use App\Models\EmpChildrens;
use App\Models\FeeHead;
use App\Models\StudentEnrollments;
use App\Models\StudentRegistration;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;

class ConcessionController extends Controller
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
            $query = Concession::with('student', 'concession')->where('created_by', Auth::user()->creatorId());
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            // $branches->prepend('Select Branch', '');
            $query = Concession::with('student', 'concession')->where('owned_by', '=', \Auth::user()->ownedId());
        }
        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
        }
        if (!empty($request->status)) {
            $query->where('status', '=', $request->status);
        }
        if (!empty($request->start_date)) {
            $query->whereDate('start_date', '>', $request->start_date);
        }
        if (!empty($request->end_date)) {
            $query->whereDate('end_date', '<', $request->end_date);
        }
        if (empty($request->start_date) || empty($request->end_date)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            // $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['start_date' => $dateFrom]);
            // $request->merge(['end_date' => $dateTo]);
            // $query->whereBetween('start_date', [$dateFrom, $dateTo]);
            $query->whereDate('start_date', '>', $request->start_date);
        }

        $concessions = $query->orderBy('id', 'Desc')->paginate(25);
        $status = [
            '' => 'All',
            'Draft' => 'Draft',
            'For Approval' => 'For Approval',
            'Approved' => 'Approved',
            'Rollbacked' => 'Rollback',
            'Canceled' => 'Canceled',
            'Rejected' => 'Rejected',
        ];
        return view('students.concession.index', compact('concessions', 'status', 'branches', 'request'));
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
            $classes = Classes::where('owned_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $classes = Classes::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        }
        $concession_policy = ConcessionPolicy::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');
        $concession_policy->prepend('Select Concession Policy', '');
        $classes->prepend('Select Class', '');
        $heads = FeeHead::where('created_by', \Auth::user()->creatorId())->get();
        return view('students.concession.create', compact('branches', 'concession_policy', 'classes', 'heads'));
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
        DB::beginTransaction();
        try {
            $validator = \Validator::make(
                $request->all(),
                [
                    'concession_id' => 'required',
                    'class_id' => 'required',
                    'student_id' => 'required',
                    // 'date' => 'required|date',
                    'period_from' => 'required|date',
                    'period_to' => 'nullable|date',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $std = StudentEnrollments::where('regId', $request->student_id)->first();
            // dd($request->all());
            $concession = new Concession();
            $concession->student_id = $request->student_id;
            $concession->class_id = $request->class_id;
            $concession->concession_order = $this->concession_order();
            $concession->concession_id = $request->concession_id;
            $concession->concession_by = \Auth::user()->name;
            $concession->type = $request->concession_type;
            $concession->session_id = @$std->session_id;
            $concession->apply_date = date('Y-m-d');
            $concession->start_date = $request->period_from;
            $concession->end_date = $request->period_to;
            $concession->remarks = $request->bill_remarks;
            $concession->owned_by = $request->branch_id;
            $concession->created_by = \Auth::user()->creatorId();
            if($request->cancle_date != null || $request->cancle_remarks){
                $prev_con = Concession::with('concession')
                ->where('student_id', $request->student_id)
                ->where('status', 'Approved')
                ->orderByDesc('id')
                ->first();
                if($prev_con){
                    $prev_con->cancel_date = $request->cancle_date;
                    $prev_con->cancel_remarks = $request->cancle_remarks;
                    $prev_con->save();
                }
            }
            $concession->save();
            DB::commit();
            return redirect()->route('concession.index')->with('success', 'Concession has been created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
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
     * @param  \App\Models\Concession  $Concession
     * @return \Illuminate\Http\Response
     */
    public function concession_order()
    {
        $concession = Concession::where('created_by', Auth::user()->creatorId())->orderBy('id', 'desc')->first();
        return $concession ? $concession->concession_order + 1 : 1;

    }
    public function show(Concession $Concession)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Concession  $Concession
     * @return \Illuminate\Http\Response
     */
    public function edit(Concession $Concession)
    {
        // if(\Auth::user()->can('edit session'))
        // {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        }
        $concession_policy = ConcessionPolicy::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');
        $classes = Classes::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $student = StudentRegistration::where('class_id', $Concession->class_id)->pluck('stdname', 'id');
        $heads = FeeHead::where('created_by', \Auth::user()->creatorId())->get();
        // $student = DB::table('student_enrollments')->join('student_registrations', 'student_enrollments.regId', '=', 'student_registrations.id')
        // ->where('student_enrollments.class_id',$Concession->class_id)->pluck('student_registrations.stname','student_registrations.id');
        return view('students.concession.edit', compact('Concession', 'branches', 'concession_policy', 'classes', 'student', 'heads'));
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Concession  $Concession
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Concession $Concession)
    {
        // if(\Auth::user()->can('edit session'))
        // {
        $validator = \Validator::make(
            $request->all(),
            [
                'concession_id' => 'required',
                'class_id' => 'required',
                'student_id' => 'required',
                // 'date' => 'required|date',
                'period_from' => 'required|date',
                'period_to' => 'nullable|date',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $Concession->student_id = $request->student_id;
        $Concession->class_id = $request->class_id;
        $Concession->concession_id = $request->concession_id;
        // $Concession->apply_date = $request->date;
        $Concession->start_date = $request->period_from;
        $Concession->end_date = $request->period_to;
        $Concession->remarks = $request->bill_remarks;
        $Concession->owned_by = $request->branch_id;
        if($request->cancle_date != null || $request->cancle_remarks){
            $prev_con = Concession::with('concession')
            ->where('student_id', $request->student_id)
            ->where('status', 'Approved')
            ->orderByDesc('id')
            ->first();
            if($prev_con){
                $prev_con->cancel_date = $request->cancle_date;
                $prev_con->cancel_remarks = $request->cancle_remarks;
                $prev_con->save();
            }
        }
        $Concession->save();

        return redirect()->route('concession.index')->with('success', 'Concession has been Updated successfully.');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Concession  $Concession
     * @return \Illuminate\Http\Response
     */
    public function destroy(Concession $Concession)
    {
        //
    }

    public function class_student(Request $request)
    {
        $student = StudentRegistration::where('class_id', $request->class_id)->get();
        // $student = DB::table('student_enrollments')->join('student_registrations', 'student_enrollments.regId', '=', 'student_registrations.id')
        // ->where('student_enrollments.class_id',$request->class_id)->get();
        return $student;
    }

    public function changeStatus($id, $status)
    {

        $concession = Concession::find($id);
        $concession->status = $status;
        $concession->active_status = 0;
        $concession->save();
        if ($status == 'Approved') {
            $concession->active_status = 1;
            $concession->approved_by = Auth::user()->name;
            $concession->approval_date = date('Y-m-d');
            $concession->save();
            $prev_con = Concession::with('concession')
                ->where('student_id', $concession->student_id)
                ->where('id', '!=', $concession->id)
                ->where('status', 'Approved')
                ->orderByDesc('id')
                ->first();
            if($prev_con){
                $prev_con->cancel_date = date('Y-m-d');
                $prev_con->status = 'Canceled';
                $prev_con->active_status = 0;
                $prev_con->cancel_remarks = 'New Concession Approved';
                $prev_con->save();
            }
            return redirect()->route('concession.index')->with('success', 'Concession Approved Successfully.');
        }
        else if ($status == 'Rejected') {
            $concession->active_status = 0;
            $concession->save();
            return redirect()->route('concession.index')->with('success', 'Concession Rejected Successfully.');
        }else{
            return redirect()->route('concession.index')->with('success', 'Concession Send For Approval Successfully.');
        }
    }


    public function cancelconcession($id)
    {
        return view('students.concession.cancel_concession', compact('id'));
    }
    public function removeconcession(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'cancel_remarks' => 'required',
                'cancel_date' => 'required|date',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $concession = Concession::findOrFail($request->id);
        $concession->cancel_date = $request->cancel_date;
        $concession->cancel_remarks = $request->cancel_remarks;
        $concession->status = 'Canceled';
        $concession->active_status = 0;
        $concession->save();
        return redirect()->route('concession.index')->with('success', 'Concession Cancel Successfully.');
    }

    public function concession_student_detail(Request $request, $id)
    {
        $student = StudentRegistration::with('concession')->find($id);

        $enroll = StudentEnrollments::with('class', 'section')
            ->where('regId', $student?->id)
            ->first();

        $concession = Concession::with('concession')
            ->where('student_id', $student?->id)
            ->where('status', 'Approved')
            ->orderByDesc('id')
            ->first();
        $teacherCh = EmpChildrens::with('employee', 'employee.designation', 'employee.userbranch')
            ->where('student_id', $student?->id)
            ->first();

        // employee tenure
        $emptenure = $teacherCh?->employee
            ? $teacherCh->employee->getEmployeeTenure($teacherCh->employee->id)
            : null;

        if ($teacherCh?->employee) {
            $teacherCh->employee->tenure = $emptenure;

            $empScaleNo = $teacherCh->employee->employee_payscale_details()
                ->orderByDesc('id')
                ->first();

            $teacherCh->employee->ScaleNo = $empScaleNo?->scale ?? null;
        }
        return response([
            'data' => $student,
            'tc' => $teacherCh,
            'enroll' => $enroll,
            'class' => $enroll?->class?->name ?? 'N/A',
            'section' => $enroll?->section?->name ?? 'N/A',
            'concession' => $concession ?? 'No Concession',
        ]);
    }

    public function concession_search(Request $request)
    {
        try {
            $titleIds = $request->input('title_id');
            $concessions = $request->input('concession');
            $head_name = $request->input('head_name');

            // Filtered data array
            $filteredData = [];
            foreach ($concessions as $index => $concession) {
                if ($concession != "" && $concession != 0) {
                    $filteredData[] = [
                        'title_id' => $titleIds[$index],
                        'concession' => $concession ?? 0,
                    ];
                }
            }
            // dd($filteredData);
            // Ensure filtered data is not empty
            if (empty($filteredData)) {
                return response()->json(['message' => 'No data to filter'], 400); // Return a meaningful response
            }

            // Query the database for matching policies
            // $policies = ConcessionPolicy::join('concession_policy_heads', 'concession_policies.id', '=', 'concession_policy_heads.concession_id')
            //     ->select('concession_policies.id', 'concession_policies.title')
            //     ->where(function($query) use ($filteredData) {
            //         foreach ($filteredData as $data) {
            //             $query->where('concession_policy_heads.head_id', $data['title_id'])->orWhere('concession_policy_heads.percentage', $data['concession']);
            //         }
            //     })
            //     ->get();
            $policies = ConcessionPolicy::join('concession_policy_heads', 'concession_policies.id', '=', 'concession_policy_heads.concession_id')
                ->select('concession_policies.id', 'concession_policies.title')
                ->where(function ($query) use ($filteredData) {
                    foreach ($filteredData as $match) {
                        $query->orWhere(function ($subQuery) use ($match) {
                            $subQuery->where('concession_policy_heads.head_id', $match['title_id'])
                                ->where('concession_policy_heads.percentage', $match['concession']);
                        });
                    }
                })
                ->groupBy('concession_policies.id', 'concession_policies.title')
                ->havingRaw('COUNT(concession_policy_heads.id) = ?', [count($filteredData)])
                ->get();
            // dd($policies);
            return response()->json($policies); // Return the filtered policies as JSON

        } catch (\Exception $e) {
            dd($e);
            \Log::error('Error filtering policies: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while filtering policies'], 500);
        }
    }

    public function concessionstatus($id)
    {
        $concession = Concession::with('student', 'class', 'concession', 'student.enrollment')->where('id', $id)->first();
        if ($concession) {
            $prev_concession = Concession::with('concession', 'student.enrollment')->where('student_id', $concession->student->id)->where('id', '!=', $concession->id)->where('status', 'Approved')->get();
            return view('students.concession.status_concession', compact('concession', 'prev_concession'));
        }
        return view('students.concession.status_concession', compact('concession'));
    }

    public function concessionrejection(Request $request, $id)
    {
        $concession = Concession::findOrFail($id);
        if ($request->reject_reason == '') {
            return redirect()->back()->with('error', 'Please give remarks to reject !');
        }
        if ($request->type == 'Rollbacked') {
            $concession->status = 'Rollbacked';
        } else {
            $concession->status = 'Rejected';
        }
        $concession->cancel_remarks = $request->reject_reason;
        $concession->active_status = 0;
        $concession->save();
        return redirect()->back()->with('success', 'Concession has been Rejected Successfully');
    }

    public function concessionReport(Request $request)
    {

        $query = Concession::with('student', 'concession', 'branches', 'class')
            ->where('created_by', \Auth::user()->creatorId());
        $brnches = User::where('id', $request->branches)->first();
        if (!empty($request->branches)) {
            $query->where('owned_by', $request->branches);
        }
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        } elseif (!empty($request->start_date)) {
            $query->where('start_date', '>=', $request->start_date);
        } elseif (!empty($request->end_date)) {
            $query->where('end_date', '<=', $request->end_date);
        } else {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['start_date' => $dateFrom]);
            $request->merge(['end_date' => $dateTo]);
            $query->whereBetween('start_date', [$dateFrom, $dateTo]);
        }

        $concessions = $query->get()->groupBy('owned_by');

        $branches_name = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
        $branches_name->prepend(\Auth::user()->name, \Auth::user()->id);
        $pdf = new Dompdf();
        $html = view('students.concession.report', compact('concessions', 'branches_name', 'request'))->render();
        $headerHtml = view('students.concession.report.pdf.header', compact('concessions', 'brnches', 'request'))->render();
        $footerHtml = view('students.concession.report.pdf.footer')->render();

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
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'potrait');
        // $dompdf->render();

        // return $dompdf->stream('concession.pdf');
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);

    }

    public function concessionorder($id)
    {
        // dd($id);
        $concession = Concession::with('student', 'class')->find($id);
        if (!$concession) {
            return redirect()->back()->with('error', __('Concession Not Found.'));
        }
        $concession_policy = ConcessionPolicy::with('concession', 'concession.student', 'concession.student.enrollment', 'concession.class', 'concession.student.session')->findOrFail($concession->concession_id);
        $concession_heads = ConcessionPolicyHead::where('concession_id', $concession_policy->id)->where('percentage', '!=', 0)->get();
        // dd($concession_policy,$concession_heads);
        $html = view('students.concession.template', [
            'data' => $concession_heads,
            'student' => @$concession->student,
            'class' => $concession->class
        ])->render();
        $headerHtml = view('students.concession.header')->render();
        $footerHtml = view('students.concession.footer')->render();
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

}

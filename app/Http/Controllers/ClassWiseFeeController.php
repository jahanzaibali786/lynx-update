<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Exports\FeeStructureListingExport;
use App\Models\ChartOfAccountType;
use App\Models\Classes;
use App\Models\StudentEnrollments;
use App\Models\StudyPack;
use Auth;
use App\Models\ClassWiseFee;
use App\Models\FeeHead;
use App\Models\Session;
use App\Models\StudentFeeStructure;
use App\Models\StudentRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Maatwebsite\Excel\Facades\Excel;

class ClassWiseFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // public function index($id)
    public function index(Request $request)
    {
        // if(\Auth::user()->can('manage session'))
        // {

        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = ClassWiseFee::orderBy('id', 'Desc');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = ClassWiseFee::orderBy('id', 'Desc');
        }
        // $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->where('name', 'Income')->first();
        // $chart_accounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
        // ->where('type', $types->id)
        // ->where('created_by', \Auth::user()->creatorId())->get();
        $heads = FeeHead::where('created_by', \Auth::user()->creatorId())->get();
        $session = Session::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('year', 'id');

        $class = [];

        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            // $session = Session::where('owned_by', '=', $request->branches)->get()->pluck('year','id');
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
        }
        if (!empty($request->session)) {
            $query->where('session_id', '=', $request->session);
        }
        if (!empty($request->class)) {
            $query->where('class_id', '=', $request->class);
        }
        $classfee = $query->get()->pluck('amount', 'head_id');

        if (empty($request->branches) && empty($request->session) && empty($request->class)) {
            // $classfee = ClassWiseFee::where('id','0')->get();
            $classfee = [];

        }
        // ->pluck('code_name', 'id');
        // $session = Session::where('created_by',Auth::user()->creatorId())->get()->pluck('title', 'id');
        // $class = Classes::where('created_by',Auth::user()->creatorId())->get()->pluck('name', 'id');

        // $class->prepend('Select Class', '');
        // $session->prepend('Select Session', '');
        return view('students.classwisefee.index', compact('heads', 'branches', 'session', 'class', 'classfee'));
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

    public function create($id)
    {
        // if(\Auth::user()->can('create session'))
        // {
        // $fee_heads = FeeHead::get()->pluck('fee_head','id');
        // $sections = Session::where('created_by',Auth::user()->creatorId())->get()->pluck('title','id');
        // return view('students.classwisefee.create',compact('fee_heads','sections','id'));
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
        // dd($request->all());
        // if(\Auth::user()->can('create session'))
        // {
        $validator = \Validator::make(
            $request->all(),
            [
                'branch' => 'required',
                'session' => 'required',
                'class' => 'required',
                'account_id' => 'required',
                'account_value' => 'required',
                // 'fee_head' => 'required|unique:class_wise_fees,head_id,NULL,id,class_id,' . $request->class_id,
            ]
            // , [
            //     'fee_head.unique' => 'This fee head already exists for the selected class.',
            // ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        if ($request->account_id > 0) {
            for ($i = 0; $i < count($request->account_id); $i++) {
                $classWiseFee = ClassWiseFee::updateOrCreate(
                    [
                        'session_id' => $request->session,
                        'class_id' => $request->class,
                        'head_id' => $request->account_id[$i],
                    ],
                    [
                        'amount' => $request->account_value[$i],
                        'discount' => '0',
                        'owned_by' => $request->branch,
                        'created_by' => Auth::user()->creatorId(),
                    ]
                );
            }
        }
        return redirect()->route('class_wise_fee.index', ['branches' => $request->branch, 'session' => $request->session, 'class' => $request->class])->with('success', 'Fee Structure has been created or updated successfully');
        // return redirect()->route('class_wise_fee.index')->with('success','Session Class Wise Fee has been created successfully');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ClassWiseFee  $classWiseFee
     * @return \Illuminate\Http\Response
     */
    public function show(ClassWiseFee $classWiseFee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ClassWiseFee  $classWiseFee
     * @return \Illuminate\Http\Response
     */
    public function edit(ClassWiseFee $classWiseFee)
    {

        // if(\Auth::user()->can('edit session'))
        // {
        $fee_heads = FeeHead::get()->pluck('fee_head', 'id');
        $sections = Session::where('created_by', Auth::user()->creatorId())->get()->pluck('year', 'id');
        return view('students.classwisefee.edit', compact('classWiseFee', 'fee_heads', 'sections'));
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
     * @param  \App\Models\ClassWiseFee  $classWiseFee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClassWiseFee $classWiseFee)
    {

        // if(\Auth::user()->can('edit session'))
        // {
        $validator = \Validator::make(
            $request->all(),
            [
                // 'class_id' => 'required',
                'head_id' => 'required|unique:class_wise_fees,head_id,' . $classWiseFee->id . ',id,class_id,' . $classWiseFee->class_id,
                'amount' => 'required',
                'session_id' => 'required',
            ],
            [
                'fee_head.unique' => 'This fee head already exists for the selected class.',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $classWiseFee->head_id = $request->head_id;
        $classWiseFee->amount = $request->amount;
        $classWiseFee->session_id = $request->session_id;
        $classWiseFee->save();

        return redirect()->route('class_fee', ['id' => $classWiseFee->class_id])->with('Class Wise Fee Head has been updated successfully');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ClassWiseFee  $classWiseFee
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClassWiseFee $classWiseFee)
    {
        //
    }

    public function sessionclass(Request $request)
    {
        if (isset($request->type) && $request->type == 'studypack') {
            //find that class in the classes 
            $class = Classes::where('id', $request->id)->get();
            $Studypack = StudyPack::where('session_id', '=', $request->session)->where('class', $class['0']->name)->get();
            // dd($class);
            $class1 = $class->pluck( 'id');
            $students = StudentEnrollments::whereIn('class_id', $class1)->where('session_id', $request->session)->get();
            $class = $class->pluck( 'name','id');
            
            // dd($students);
        } else {
            $Studypack = [];
            $class = Classes::where('owned_by', '=', $request->id)->where('active_status', 1)->get();
            if (empty($request->id) || is_null($request->id) && Auth::user()->type == 'company') {
                $students = StudentRegistration::where('created_by', \Auth::user()->creatorId())->where('student_status', '!=', 'Registered')->get();
            } else {
                $students = StudentRegistration::where('owned_by', '=', $request->id)->where('student_status', '!=', 'Registered')->get();
            }
        }
        $session = Session::where('created_by', \Auth::user()->creatorId())->get();
        $result = [
            'status' => 'success',
            'session' => $session,
            'class' => $class,
            'Studypack' => $Studypack,
            'students' => $students,
        ];
        return response()->json($result);
    }

    public function getClassStudents(Request $request)
    {
        if ($request->ajax()) {
            if ($request->type == 'registration') {
                $students = StudentRegistration::where('class_id', $request->class_id)
                    ->where('student_status', 'Registered')
                    ->where('created_by', \Auth::user()->creatorId())
                    ->get()
                    ->mapWithKeys(function ($student) {
                        return [$student->id => $student->stdname . ' s/d/o ' . $student->fathername];
                    });
            } else {
                $students = StudentRegistration::where('class_id', $request->class_id)
                    ->where('student_status', 'Enrolled')
                    ->where('created_by', \Auth::user()->creatorId())
                    ->get()
                    ->mapWithKeys(function ($student) {
                        return [$student->id => $student->roll_no . ' - ' . $student->stdname . ' s/d/o ' . $student->fathername];
                    });
            }
            // dd($students);
            // $students = StudentEnrollments::where('class_id', $request->class_id)->pluck('name', 'id');
            return response()->json(['status' => 'success', 'students' => $students]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid request.']);
    }

    public function classStudents(Request $request)
    {
        if ($request->ajax()) {
            // if($request->type == 'registration'){
            //     $students = StudentRegistration::select(\DB::raw('CONCAT(stdname, " s/d/o ", fathername) AS stdname'), 'id')->where('class_id', $request->class_id)->where('student_status','Registered')->where('created_by', '=', \Auth::user()->creatorId())->pluck('stdname', 'id');
            // }else{
            $students = StudentRegistration::where('class_id', $request->class_id)->where('student_status', 'Enrolled')->where('created_by', '=', \Auth::user()->creatorId())->get();
            // }
            // $students = StudentEnrollments::where('class_id', $request->class_id)->pluck('name', 'id');
            return response()->json(['status' => 'success', 'students' => $students]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid request.']);
    }
    public function getClasswithdrawStudents(Request $request)
    {
        if ($request->ajax()) {
            $status = $request->input('status');
            $query = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->whereNotNull('roll_no')
                ->where('class_id', '=', $request->class_id);
            if ($status == 'active') {
                $query->whereDoesntHave('withdrawal');
            } else if ($status == 'withdraw') {
                $query->whereHas('withdrawal');
            }
            $students = $query->get()->pluck('stdname', 'roll_no');
            ;
            return response()->json(['status' => 'success', 'students' => $students]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid request.']);
    }

    public function studentfeestructure(Request $request)
    {
        $session = Session::where('owned_by', '=', $request->id)->get();
        $class = Classes::where('owned_by', '=', $request->id)->get();

        $result = [
            'status' => 'success',
            'session' => $session,
            'class' => $class,
        ];
        return response()->json($result);
    }

    public function student_fee_generate($id)
    {
        $student = StudentRegistration::where('id', $id)->first();
        $classfee = ClassWiseFee::with('account')->where('session_id', $student->session_id)->where('class_id', $student->class_id)->where('owned_by', $student->owned_by)->get();
        if (!empty($classfee)) {
            foreach ($classfee as $fee) {
                $keys = [
                    'reg_id' => $student->id,
                    'branch_id' => $student->owned_by,
                    'head_id' => $fee['head_id'],
                ];
                $values = [
                    'amount' => $fee['amount'],
                    'class_id' => $student->class_id,
                    'owned_by' => $student->owned_by,
                    'created_by' => $student->created_by,
                ];
                $sfs = StudentFeeStructure::where($keys)->first();
                if ($sfs) {
                    if ($sfs->checked_status == 1) {
                        $sfs->update($values);
                    }
                } else {
                    StudentFeeStructure::create(array_merge($keys, $values));
                }
            }
        }

        return redirect()->route('registration.show', ['registration' => $student->id])->with('success', 'Student Fee Structure Genereated Successfull.');
    }

    public function classWiseFeeReport(Request $request)
    {

        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = ClassWiseFee::with('feehead', 'class')->orderBy('id', 'Desc');
            $brnches_name = User::where('id', $request->branches)->first();
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = ClassWiseFee::with('feehead', 'class')->orderBy('id', 'Desc');
            $brnches_name = User::where('id', $request->branches)->first();
        }
        ;

        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
        }
        if (!empty($request->session)) {
            $query->where('session_id', '=', $request->session);
        }
        if (!empty($request->class)) {
            $query->where('class_id', '=', $request->class);
        }
        $classfee = $query->get()->pluck('amount', 'head_id');

        if (empty($request->branches) && empty($request->session) && empty($request->class)) {
            // $classfee = ClassWiseFee::where('id','0')->get();
            $classfee = [];

        }

        $class_wise_fee = $query->get()->groupBy('owned_by');
        $pdf = new Dompdf();
        $html = view('students.classwisefee.report', compact('class_wise_fee', 'branches', 'request'))->render();
        $headerHtml = view('students.classwisefee.report.pdf.header', compact('class_wise_fee', 'brnches_name', 'request'))->render();
        $footerHtml = view('students.classwisefee.report.pdf.footer')->render();

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

        // return $dompdf->stream('class_wise_fee.pdf');
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
    public function feestructurelisting(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', '');
            $query = ClassWiseFee::with('feehead', 'class', 'session')->orderBy('id', 'Desc');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('All Branches', '');
            $query = ClassWiseFee::with('feehead', 'class', 'session')->orderBy('id', 'Desc');
        }

        $heads = FeeHead::where('created_by', \Auth::user()->creatorId())->get();
        $session = Session::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('year', 'id');

        if (!empty($request->branches) && is_array($request->branches)) {
            $query->whereIn('owned_by', $request->branches);
            $class = Classes::whereIn('owned_by', $request->branches)->get()->pluck('name', 'id');
        }


        if (!empty($request->session) && is_array($request->session)) {
            $query->whereIn('session_id', $request->session);

        }
        if (!empty($request->branches) || !empty($request->session)) {
            $class_wise_fee = $query->get()->groupBy('owned_by')->map(function ($group) {
                return $group->groupBy('session_id');
            });
            $classfee = $query->get()->pluck('amount', 'head_id');
        } else {
            $class_wise_fee = [];
            $classfee = [];
        }
        return view('students.classwisefee.list', compact('heads', 'branches', 'session', 'classfee', 'class_wise_fee'));
    }


    public function feeStructureReport(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', '');
            $query = ClassWiseFee::with('feehead', 'class')->orderBy('id', 'Desc');
            $brnches_name = User::where('id', $request->branches)->first();
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('All Branches', '');
            $query = ClassWiseFee::with('feehead', 'class')->orderBy('id', 'Desc');
            $brnches_name = User::where('id', $request->branches)->first();
        }
        ;
        $heads = FeeHead::where('created_by', \Auth::user()->creatorId())->get();

        if (!empty($request->branches) && is_array($request->branches)) {
            $query->whereIn('owned_by', $request->branches);
            $class = Classes::whereIn('owned_by', $request->branches)->get()->pluck('name', 'id');
        }


        if (!empty($request->session) && is_array($request->session)) {
            $query->whereIn('session_id', $request->session);
        }
        if (!empty($request->class)) {
            $query->where('class_id', '=', $request->class);
        }
        $classfee = $query->get()->pluck('amount', 'head_id');

        if (empty($request->branches) && empty($request->session) && empty($request->class)) {
            // $classfee = ClassWiseFee::where('id','0')->get();
            $classfee = [];

        }

        $class_wise_fee = $query->get()->groupBy('owned_by')->map(function ($group) {
            return $group->groupBy('session_id');
        });
                $branchName = 'All Branches'; // Default value

if (!empty($request->branches)) {
    // Convert to array if it's not already (for single selection case)
    $selectedBranches = is_array($request->branches) ? $request->branches : [$request->branches];
    
    $query->whereIn('owned_by', $selectedBranches);
    $class = Classes::whereIn('owned_by', $selectedBranches)->get()->pluck('name', 'id');
    
    // Get branch names
    if (count($selectedBranches) > 1) {
        $branchName = 'All Branches';
    } else {
        $branch = User::find($selectedBranches[0]);
        $branchName = $branch ? $branch->name : 'All Branches';
    }
}
        
        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Fee Structure Listing Report';
            return Excel::download(new FeeStructureListingExport($class_wise_fee, $branchName, $branches, $heads, $report_name, $request, $request->all()), 'Fee_structure_listing_report.xlsx');
        }
        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Fee Structure Listing Report';
            return Excel::download(new FeeStructureListingExport($class_wise_fee, $branchName, $branches, $heads, $report_name, $request, $request->all()), 'Fee_structure_listing_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        $report_name = 'Fee Structure';
        $pdf = new Dompdf();
        $html = view('students.classwisefee.listreport', compact('class_wise_fee', 'branches', 'request', 'heads'))->render();
        $headerHtml = view('students.classwisefee.report.pdf.header', compact('class_wise_fee', 'brnches_name', 'request', 'report_name'))->render();
        $footerHtml = view('students.classwisefee.report.pdf.footer')->render();

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
        // $dompdf->setPaper('A4', 'landscape');
        // $dompdf->render();

        // return $dompdf->stream('class_wise_fee.pdf');
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
}

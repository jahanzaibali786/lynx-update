<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\EmployeeScale;
use App\Models\EmployeeScaleHeads;
use App\Exports\EmployeeScaleReportExport;
use App\Models\SalaryHeads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Maatwebsite\Excel\Facades\Excel;
class EmployeeScaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage employee')) {
            $query = EmployeeScale::with('employeeScaleHeads','employeepayScaledetailHeads')
            ->where('created_by', \Auth::user()->creatorId());
      
            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend(__('Select Department'), '');
            $heads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
            if (!empty($request->scale_no)) {
                $query->where('scale_no', $request->scale_no);
            }
            if (!empty($request->effect_from)) {
                $query->where('effect_from', $request->effect_from);
            }
            if (!empty($request->department)) {
                $query->where('department_id', $request->department);
            }
            if ($request->adhoc != null) {
                $query->where('adhoc', $request->adhoc);
            }
            if ($request->status != null && $request->status != 'all') {
                $query->where('status', $request->status);
                $stat = $request->status;
            }elseif($request->status == 'all'){
                $stat = 'all';
            }
            else{
                $query->where('status', 1);
                $stat = 1;
            }
            // dd($request->all(),$query->get());
            if ($request->has('export') && $request->export == 'excel') {
                $employee_scales = $query->get();  
                return Excel::download(new EmployeeScaleReportExport($employee_scales,$heads), 'employee_scales.xlsx');
            }
            if ($request->has('export') && $request->export == 'pdf') {
                $employee_scales = $query->get();  
                return Excel::download(new EmployeeScaleReportExport($employee_scales,$heads), 'employee_scales.pdf', \Maatwebsite\Excel\Excel::MPDF);
            }
            foreach ($heads as $account) {
                $headKey = 'head_' . $account->id;
                if (!is_null($request->input($headKey))) {
                    $query->whereHas('employeeScaleHeads', function ($q) use ($request, $headKey, $account) {
                        $q->where('head', $account->id)
                          ->where('head_value', $request->input($headKey));
                    });
                }
            }
            

            $employee_scales = $query->get();

            return view('scale.index', compact('employee_scales', 'department', 'heads', 'stat'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function generateReport(Request $request)
    {
        $query = EmployeeScale::with('employeeScaleHeads')->where('status', 1)->where('created_by', \Auth::user()->creatorId());
            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $heads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
            if (!empty($request->scale_no)) {
                $query->where('scale_no', $request->scale_no);
            }
            if (!empty($request->effect_from)) {
                $query->where('effect_from', $request->effect_from);
            }
            if (!empty($request->department)) {
                $query->where('department_id', $request->department);
            }
            if (!empty($request->adhoc)) {
                $query->where('adhoc', $request->adhoc);
            }
            if (!empty($request->status)) {
                $query->where('status', $request->status);
            }
            foreach ($heads as $account) {
                $headKey = 'head_' . $account->id;
                if (!is_null($request->input($headKey))) {
                    $query->whereHas('employeeScaleHeads', function ($q) use ($request, $headKey, $account) {
                        $q->where('head', $account->id)
                          ->where('head_value', $request->input($headKey));
                    });
                }
            }

        $employee_scales = $query->get();
        $pdf = new Dompdf();
        $texthtml = view('scale.report', compact('employee_scales','heads'))->render();
        $headerHtml = view('employee.emp_salary_detail.pdf.header')->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        $html = '<html><head>
            <style>
                @page {
                    margin-top: 100px;
                    margin-bottom: 100px;
                }
                body { font-family: sans-serif; font-size: 12px; }
                .header {
                    position: fixed;
                    top: -100px;
                    left: 0;
                    right: 0;
                    height: 100px;
                    text-align: center;
                }
                .footer {
                    position: fixed;
                    bottom: -60px;
                    left: 0;
                    right: 0;
                    height: 50px;
                    text-align: center;
                    font-size: 10px;
                    color: #888;
                }
                .logo{
                    position: absolute;
                    top:40px;
                    }
            </style>
            </head>
            <body>
                <div class="header">' . $headerHtml . '</div>
                <div class="footer">' . $footerHtml . '</div>
                ' . $texthtml . '
            </body></html>';
        $pdfOptions = new Options();
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('defaultFont', 'Helvetica');
        $pdf->setOptions($pdfOptions);

        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->stream('employee_scale_report.pdf');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (\Auth::user()->can('create trainer')) {
            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $heads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
            return view('scale.create', compact('department', 'heads'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create trainer')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'scale_no' => 'required|unique:employee_scales',
                    'department_id' => 'required|numeric',
                    'account_id' => 'required',
                    'type' => 'required',
                    'account_value' => 'required',
                    'effect_from' => 'required|date',
                    'adhoc' => 'required|numeric',
                    'status' => 'required|numeric',
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 400);
            }
            DB::beginTransaction();
            try {
                $employee_scales = new EmployeeScale();
                $employee_scales->scale_no = $request->scale_no;
                $employee_scales->type = $request->type;
                $employee_scales->effect_from = $request->effect_from;
                $employee_scales->adhoc = $request->adhoc;
                $employee_scales->department_id = $request->department_id;
                $employee_scales->status = $request->status;
                $employee_scales->owned_by = \Auth::user()->ownedId();
                $employee_scales->created_by = \Auth::user()->creatorId();
                $employee_scales->save();
                if ($employee_scales) {
                    for ($i = 0; $i < count($request->account_id); $i++) {
                        EmployeeScaleHeads::Create([
                            'scale_id' => $employee_scales->id,
                            'scale_no' => $employee_scales->scale_no,
                            'head' => $request->account_id[$i],
                            'head_value' => $request->account_value[$i] ?? 0,
                            'owned_by' => \Auth::user()->ownedId(),
                            'created_by' => \Auth::user()->creatorId(),
                        ]);
                    }
                }
                DB::commit();
                return response()->json(['status' => 'success'], 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => __('Permission denied.')], 403);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmployeeScale  $employeeScale
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeScale $employeeScale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EmployeeScale  $employeeScale
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeScale $employeeScale)
    {
        if (\Auth::user()->can('edit trainer')) {
            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $heads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
            return view('scale.edit', compact('employeeScale', 'department', 'heads'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmployeeScale  $employeeScale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit trainer')) {
          
            $validator = \Validator::make($request->all(), [
                'scale_no' => 'required',
                // 'type' => 'required',
                'department_id' => 'required|numeric',
                'account_id' => 'required|array',
                'account_value' => 'required|array',
                'adhoc' => 'required|numeric',
                'status' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
    
            DB::beginTransaction();
            try {
                $employee_scale = EmployeeScale::findOrFail($id);
                $employee_scale->adhoc = $request->adhoc;
                $employee_scale->type = $request->type;
                $employee_scale->status = $request->status;
                $employee_scale->department_id = $request->department_id;
                $employee_scale->owned_by = \Auth::user()->ownedId();
                $employee_scale->created_by = \Auth::user()->creatorId();
                $employee_scale->save();
            
                EmployeeScaleHeads::where('scale_no', $employee_scale->scale_no)->delete();
                for ($i = 0; $i < count($request->account_id); $i++) {
                    EmployeeScaleHeads::create(
                        [
                            'scale_id' => $employee_scale->id,
                            'scale_no' => $employee_scale->scale_no,
                            'head' => $request->account_id[$i],
                            'head_value' => $request->account_value[$i] ?? 0,
                            'owned_by' => \Auth::user()->ownedId(),
                            'created_by' => \Auth::user()->creatorId(),
                        ]
                    );
                }

                DB::commit();
                return redirect()->route('employee_scale.index')->with('success', __('Employee Scale successfully updated.'));
            } catch (\Exception $e) {
                DB::rollback();
                return back()->withInput()->with('error', 'Error occurred while updating Employee Scale: ' . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmployeeScale  $employeeScale
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeScale $employeeScale)
    {
        //
    }
    public function getDeptWiseScales(Request $request) {
        if (\Auth::user()->type == 'company') { 
            $employee_scales = EmployeeScale::where('department_id', $request->dept) 
                ->where('status', 1) 
                ->where('created_by', \Auth::user()->creatorId()) 
                ->get(['id', 'scale_no as title']); 
        } else { 
            $employee_scales = EmployeeScale::where('department_id', $request->dept) 
                ->where('status', 1)
                ->where('owned_by', \Auth::user()->ownedId())
                ->get(['id', 'scale_no as title']);
        }
        return response()->json(['success' => true, 'data' => $employee_scales]);
    }
}

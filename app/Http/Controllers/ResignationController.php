<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeFinalSettlement;
use App\Models\EmployeeLeaves;
use App\Models\Resignation;
use App\Models\User;
use App\Models\EmpChildrens;
use App\Models\Concession;
use App\Models\Utility;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Dompdf\Dompdf;
use Dompdf\Options;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ResignationReportExport;
class ResignationController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage resignation')) {
            // Filter branches and resignations (your existing logic)
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')
                    ->where('created_by', '=', \Auth::user()->creatorId())
                    ->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $query = Resignation::with('employee', 'employee.finalsettlement')
                    ->where('created_by', '=', \Auth::user()->creatorId());
            } elseif (\Auth::user()->type == 'Employee') {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $emp = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $query = Resignation::with('employee', 'employee.finalsettlement')
                    ->where('created_by', '=', \Auth::user()->creatorId())
                    ->where('employee_id', '=', $emp->id);
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = Resignation::with('employee', 'employee.finalsettlement')
                    ->where('owned_by', '=', \Auth::user()->ownedId());
            }
            
            if (!empty($request->branches)) {
                $query->whereHas('employee', function ($q) use ($request) {
                    $q->where('owned_by', '=', $request->branches);
                });
            }

            if ($request->filled('is_print') && $request->is_print == 1) {
                $resignations = $query->get();
                $bodyHtml = view('resignation.print', compact('resignations', 'branches'))->render();
                $headerHtml = view('employee.report.pdf.header')->render();
                $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
                $finalHtml = '
                <html><head>
                <style>
                    @page {
                        margin-top: 100px;
                        margin-bottom: 100px;
                    }
                    body { font-family: sans-serif; font-size: 12px; }
                    .header {
                        position: fixed;
                        top: -60px;
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
                </style>
                </head>
                <body>
                    <div class="header">' . $headerHtml . '</div>
                    <div class="footer">' . $footerHtml . '</div>
                    ' . $bodyHtml . '
                </body></html>';
                // dd($finalHtml);

                $options = new Options();
                $options->set('isHtml5ParserEnabled', true);
                $options->set('isRemoteEnabled', true);
                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($finalHtml);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                return $dompdf->stream('resignation_report.pdf', ['Attachment' => false]);
            }
            $resignations = $query->orderBy('id', 'Desc')->get();

            if ($request->has('export') && $request->export == 'excel') {
                return Excel::download(new ResignationReportExport($resignations, $branches, "Employee Resignation Report"), 'resignation_report.xlsx');
            }

            if ($request->has('export') && $request->export == 'pdf') {
                return Excel::download(new ResignationReportExport($resignations, $branches, "Employee Resignation Report"), 'resignation_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
            }

            return view('resignation.index', compact('resignations', 'branches'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }


    public function create()
    {
        if (\Auth::user()->can('create resignation')) {
            if (\Auth::user()->type == 'company') {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
            } else {
                $employees = Employee::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
            }
            return view('resignation.create', compact('employees', 'branches'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create resignation')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'notice_date' => 'date',
                    'resignation_date' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $resignation = new Resignation();
            $user = \Auth::user();

            if ($user->type == 'Employee') {
                $employee = Employee::where('user_id', $user->id)->first();
                $resignation->employee_id = $employee->id;
                $resignation->branch_id = $employee->id;
            } else {
                $resignation->branch_id = $request->branches;
                $resignation->employee_id = $request->employee_id;
            }
            $resignation->notice_date = $request->notice_date;
            $resignation->resignation_date = $request->resignation_date;
            $resignation->last_attendance_date = $request->last_attendance_date;
            $resignation->description = $request->description;
            $resignation->owned_by = \Auth::user()->ownedId();
            $resignation->created_by = \Auth::user()->creatorId();

            $resignation->save();
            // if ($resignation) {
            //     $employee = Employee::where('id', $request->employee_id)->first();
            //     $employee->is_res_ter = 1;
            //     $employee->save();
            // }
            $setings = Utility::settings();
            if ($setings['resignation_sent'] == 1) {
                $employee = Employee::find($resignation->employee_id);
                $resignation->name = $employee->name;
                $resignation->email = $employee->email;

                $resignationArr = [
                    'resignation_email' => $employee->email,
                    'assign_user' => $employee->name,
                    'resignation_date' => $resignation->resignation_date,
                    'notice_date' => $resignation->notice_date,

                ];
                //                dd($resignationArr);
                $resp = Utility::sendEmailTemplate('resignation_sent', [$employee->email], $resignationArr);



                return redirect()->route('resignation.index')->with('success', __('Resignation  successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));

            }

            return redirect()->route('resignation.index')->with('success', __('Resignation  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Resignation $resignation)
    {
        $resignation = Resignation::with('employee')->where('id', $resignation->id)->first();
        return view('resignation.show', compact('resignation'));
    }

    public function approval(Request $request, $id)
    {
        // dd($request->all());
        $resignation = Resignation::find($id);
        // dd($resignation,$id);
        if (!empty($request->status)) {
            $resignation->status = $request->status == '1' ? 1 : 2;
        }
        $resignation->appr_rej_desc = $request->appr_rej_desc;
        $resignation->save();
        if($resignation){
            $empChild = EmpChildrens::where('emp_id', $resignation->employee_id)->get();
            if($empChild){
                foreach($empChild as $child){
                    $cildConcession = Concession::where('student_id', $child->student_id)->where('end_date', '>=', date('Y-m-d', strtotime($resignation->resignation_date)))->where('status', 'Approved')->first();
                    if($cildConcession){
                        $cildConcession->end_date = date('Y-m-d', strtotime($resignation->resignation_date));
                        $cildConcession->status = 'Cancelled';
                        $cildConcession->save();
                    }
                }
            }
        }
        return redirect()->route('resignation.index')->with('success', __('Resignation Update successfully'));
    }
    public function edit(Resignation $resignation)
    {
        if (\Auth::user()->can('edit resignation')) {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            } else {
                $employees = Employee::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');

            }
            if ($resignation->created_by == \Auth::user()->creatorId()) {

                return view('resignation.edit', compact('resignation', 'employees', 'branches'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Resignation $resignation)
    {
        if (\Auth::user()->can('edit resignation')) {
            if ($resignation->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [

                        'notice_date' => 'date',
                        'resignation_date' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                if (\Auth::user()->type != 'employee') {
                    $resignation->branch_id = $request->branches;
                    $resignation->employee_id = $request->employee_id;
                }


                $resignation->notice_date = $request->notice_date;
                $resignation->resignation_date = $request->resignation_date;
                $resignation->last_attendance_date = $request->last_attendance_date;
                $resignation->description = $request->description;

                $resignation->save();

                return redirect()->route('resignation.index')->with('success', __('Resignation successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Resignation $resignation)
    {
        if (\Auth::user()->can('delete resignation')) {
            if ($resignation->created_by == \Auth::user()->creatorId()) {
                $resignation->delete();

                return redirect()->route('resignation.index')->with('success', __('Resignation successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function final_settlement(Request $request, $id)
    {
        $data = $request->all();
        $EmployeefinalSettlement = EmployeeFinalSettlement::with('employee', 'employee_payscale_details', 'designation', 'employee_payscale_details.scale', 'finalsettlementHeads', 'finalsettlementHeads.salaryHead', 'final_set_adj_ded', 'resignation')->where('emp_id', $id)->first();
        $data['employee'] = @$EmployeefinalSettlement->employee ?? null;
        $lastPayscaleDetail = @$EmployeefinalSettlement->employee_payscale_details ? $EmployeefinalSettlement->employee_payscale_details->last() : null;
        $data['lastPayscaleDetail'] = $lastPayscaleDetail;
        $data['EmployeefinalSettlement'] = $EmployeefinalSettlement;
        if (!$lastPayscaleDetail) {
            return response()->json(['error' => 'Scale Not attached . please attach payscale First.'], 404);
        }
        
        $headerHtml = view('employee.emp_salary_detail.pdf.header')->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        $html = view('resignation.final_settlement', $data)->render();
        $html = '<html><head>
             <style>
                 @page {
                     margin-top: 100px;
                     margin-bottom: 50px;
                 }
                 .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
                 .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
             </style>
             </head><body>
             ' . $headerHtml . '
             <div class="footer">' . $footerHtml . '</div>
             ' . $html . '
             </body></html>';
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);
    }
}

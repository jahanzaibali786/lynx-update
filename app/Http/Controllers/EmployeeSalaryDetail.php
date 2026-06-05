<?php

namespace App\Http\Controllers;

use App\Exports\DeductionSheetExport;
use App\Exports\GrossSheetExport;
use App\Exports\SalarySheetExport;
use App\Exports\PaymodesSheetExport;
use App\Exports\AdvanceSheetExport;
use App\Models\AppointmentLetter;
use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeMonthlySalary;
use App\Models\EmployeeMonthlySalaryAttendance;
use App\Models\EmployeeMonthlySalaryHeads;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeScale;
use App\Models\EmployeeScaleHeads;
use App\Models\EmployeePayscaleDetail;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\SalaryDeductionDetail;
use App\Models\SalaryHeads;
use App\Exports\SalarySlipExport;
// use App\Models\SchoolDetail;
use App\Models\SchoolDetails;
use App\Models\User;
use Carbon\Carbon;
// use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalaryHistoryExport;
use App\Exports\EmployeeSalaryDetailReportExport;
use App\Models\SalaryHistoryReportExport;
use Str;

class EmployeeSalaryDetail extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage employee')) {
                $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $departments->prepend('All', 'all');
                $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $designations->prepend('All', 'all');

            if (\Auth::user()->type == 'Employee') {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = Employee::where('user_id', '=', Auth::user()->id);
            } else if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $query = Employee::with([
                    'employee_payscale_details',
                ])->where('created_by', \Auth::user()->creatorId());
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $query = Employee::with([
                    'employee_payscale_details',
                ])->where('owned_by', \Auth::user()->ownedId());
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            if (!empty($request->department_id) && $request->department_id != 'all') {
                $query->where('department_id', '=', $request->department_id);
            }
            if (!empty($request->designation_id) && $request->designation_id != 'all') {
                $query->where('designation_id', '=', $request->designation_id);
            }
            if (!empty($request->status)) {
                $query->where('is_res_ter', '=', $request->status);
            }else
            {
                $query->where('is_res_ter', '=', 0);
            }
            $heads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
            
            if ($request->has('export') && $request->export == 'excel') {
                // $employees = $query->orderBy('id', 'Desc')->get();
                return Excel::download(new EmployeeSalaryDetailReportExport($query->orderBy('id', 'Desc'), $heads), 'employee_salary_detail.xlsx');
            }

            // if ($request->has('export') && $request->export == 'pdf') {
            //     // $employees = $query->orderBy('id', 'Desc')->get();
            //     return Excel::download(new EmployeeSalaryDetailReportExport($query->orderBy('id', 'Desc'), $heads), 'employee_salary_detail.pdf');
            // }
            $employees = $query->orderByDesc('id')->get();


            return view('employee.emp_salary_detail.index', compact('employees', 'branches', 'departments', 'designations'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
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
        \DB::beginTransaction();
        try {
            $request->validate([
                'paymode' => 'required|string',
                'account_number' => 'required|string',
                'accounts' => 'required|integer',
                'department_id' => 'required|integer',
                'pay_scale' => 'required|integer',
                'effect_from' => 'required|date',
                'emp_sec' => 'numeric',
                'working_days' => 'required|numeric',
                // 'other_add' => 'numeric',
                'security_receive_account' => 'required|integer',
                'eobi' => 'numeric',
                'eobi_employer' => 'numeric',
                'eobi_payable_account' => 'integer',
                'pessi' => 'numeric',
                'pessi_employer' => 'numeric',
                'pessi_payable_account' => 'integer',
                'net' => 'required|numeric',
                'net_payable_account' => 'required|integer',
            ]);
            // dd($request->all());
            $scale = EmployeePayscaleDetail::where('employee_id', $request->employee_id)->orderBy('id', 'Desc')->first();
            $employee = Employee::where('id', $request->employee_id)->first();
            // check if employee departement and pay scale department is same or not
            if($employee->department_id != $request->department_id){
                return redirect()->back()->with('error', 'Employee department and pay scale department must be same.');
            }

            $appLetter = AppointmentLetter::where('type', Str::lower($employee->category))->latest()->first();
            if (round(@$scale->net) == round($request->net) && $scale->id == $request->pay_scale && date('Y-m-d', strtotime(@$scale->updated_at)) == date('Y-m-d', strtotime($request->effect_from)) && $scale->working_days == $request->working_days) {
                
                $scale->security_receive_account  = $request->security_receive_account;
                $scale->tax_payable_account  = $request->tax_payable_account;
                $scale->eobi_payable_account  = $request->eobi_payable_account;
                $scale->pessi_payable_account = $request->pessi_payable_account;
                $scale->other_dedu_payable_account = $request->other_dedu_payable_account;
                $scale->advance_payable_account = $request->advance_payable_account;
                $scale->net_payable_account = $request->net_payable_account;
                $scale->save();
            } else {

                $payscaleattach = EmployeePayscaleDetail::create([
                    'employee_id' => $request->employee_id,
                    'appletter' => $appLetter->id,
                    'paymode' => $request->paymode,
                    'account_number' => $request->account_number,
                    'account_id' => $request->accounts,
                    'department_id' => $request->department_id,
                    'pay_scale_id' => $request->pay_scale,
                    'effect_from' => $request->effect_from,
                    'working_days' => $request->working_days,
                    'drns' => $request->drns,
                    'conv' => $request->conv,
                    'misc' => $request->misc,
                    'other_add' => $request->other_add,
                    'chaild_concession' => $request->chaild_concession,
                    'emp_sec' => $request->emp_sec,
                    'security_receive_account' => $request->security_receive_account,
                    'itax' => $request->itax,
                    'tax_payable_account' => $request->tax_payable_account,
                    'eobi' => $request->eobi,
                    'eobi_employer' => $request->eobi_employer,
                    'eobi_payable_account' => $request->eobi_payable_account,
                    'pessi' => $request->pessi,
                    'pessi_employer' => $request->pessi_employer,
                    'pessi_payable_account' => $request->pessi_payable_account,
                    'other_deduction' => $request->other_deduction,
                    'other_dedu_payable_account' => $request->other_dedu_payable_account,
                    'advance' => $request->advance,
                    'advance_payable_account' => $request->advance_payable_account,
                    'net' => round($request->net),
                    'net_payable_account' => $request->net_payable_account,
                ]);
                if($payscaleattach){
                    $employee->eobi = $request->eobi_percentage;
                    $employee->eobi_employer = $request->eobi_employer_percentage;
                    $employee->pessi = $request->pessi_percentage;
                    $employee->pessi_employer = $request->pessi_employer_percentage;
                    $employee->security = $request->emp_sec_percentage;
                    $employee->save();
                }
            }
            \DB::commit();
            return redirect()->back()->with('success', 'Employee Pay Scale Details saved successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            dd($e);
            return redirect()->back()->with('error', $e);
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
        $empId = Crypt::decrypt($id);
        $employee_dept = Employee::where('id', $empId)->first();
        $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        if (\Auth::user()->type == 'company') {
            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                ->where('created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $payableaccounts = ChartOfAccount::where('created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $payableaccounts->prepend('Select Accounts', '');
            $payscales = EmployeeScale::with('employeeScaleHeads')
            ->where('department_id', $employee_dept->department_id)
            ->where('status', 1)
            ->where('created_by', \Auth::user()->creatorId())->get()->pluck('scale_no', 'id');
            $payscales->prepend('Select Scale', '');
        } else {
            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                ->where('created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $payableaccounts = ChartOfAccount::where('created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $payableaccounts->prepend('Select Accounts', '');
            $payscales = EmployeeScale::with('employeeScaleHeads')
            ->where('department_id', $employee_dept->department_id)
            ->where('status', 1)
            ->where('created_by',\Auth::user()->creatorId())->get()->pluck('scale_no', 'id');
            $payscales->prepend('Select Scale', '');
        }

        $employee = Employee::with([
            'employee_payscale_details' => function ($q) {
                $q->latest()->limit(1);
            },
            'employee_payscale_details.scale' => function ($q) use ($employee_dept) {
                $q->where('department_id', $employee_dept->department_id);
            },
        ])->findOrFail($empId);

        // dd($accounts);
        $branches_school = SchoolDetails::where('branch_id', $employee->owned_by)->first();
        $lastPayscaleDetail = $employee->employee_payscale_details->last();
        $eobiValue = ($employee->eobi / 100) * $branches_school->eobi_values;
        $eobiEmployerValue = ($employee->eobi_employer / 100) * $branches_school->eobi_values;
        $pessiValue = ($employee->pessi / 100) * $branches_school->pessi_values;
        $pessiEmployerValue = ($employee->pessi_employer / 100) * $branches_school->pessi_values;

        $companySetEobi = $branches_school->eobi_values;
        $companySetPessi = $branches_school->pessi_values;
        $companySetEobiEmployer = $branches_school->eobi_values ?? 0;
        $companySetPessiEmployer = $branches_school->pessi_values ?? 0;

        // dd($eobiValue,$pessiValue,$pessiEmployerValue,$eobiEmployerValue,$employee);
        return view('employee.emp_salary_detail.show', compact('accounts', 'eobiValue', 'pessiValue', 'pessiEmployerValue', 'eobiEmployerValue', 'lastPayscaleDetail', 'departments', 'payscales', 'employee', 'payableaccounts', 'branches_school', 'companySetEobi', 'companySetPessi', 'companySetEobiEmployer', 'companySetPessiEmployer'));
    }

    public function getPayScaleHeads(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $payScale = EmployeeScale::with( 'employeeScaleHeads')
            ->where('department_id', $request->department_id)
            ->where('created_by', \Auth::user()->creatorId())
                ->where('id', $request->id)->first();
            $employeeScaleHeads = EmployeeScaleHeads::with('salaryHeads')->where('scale_id',$payScale->id)
            ->where('created_by', \Auth::user()->creatorId())->get();
            } else {
                $payScale = EmployeeScale::with( 'employeeScaleHeads')
                ->where('department_id', $request->department_id)
                ->where('created_by', \Auth::user()->creatorId())
                ->where('id', $request->id)->first();
                $employeeScaleHeads = EmployeeScaleHeads::with('salaryHeads')->where('scale_id',$payScale->id)
                ->where('created_by', \Auth::user()->creatorId())->get();
            }
            // dd($payScale);

        if ($payScale) {
            return response()->json(['success' => true, 'data' => $employeeScaleHeads, 'payscale' => $payScale]);
        }

        return response()->json(['success' => false, 'message' => 'Pay scale not found']);
    }

    public function finalize_salary(Request $request)
    {
        $rows = $request->input('rows', []);
        $date = $request->input('date');
        $action = $request->input('action') === 'unfinalize' ? 'unfinalize' : 'finalize';

        if (!$rows) {
            return response()->json(['success' => false, 'message' => __('No entries selected for finalization.')]);
        }

        $errors = [];
        $finalizedIds = [];

        \DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                $attendanceId = is_array($row) ? ($row['id'] ?? null) : $row;
                $employeeId = is_array($row) ? ($row['employee_id'] ?? null) : null;
                $rowDate = is_array($row) ? ($row['date'] ?? $date) : $date;

                $salaryatt = null;
                if (!empty($attendanceId)) {
                    $salaryatt = EmployeeMonthlySalaryAttendance::where('id', $attendanceId)->first();
                }

                if (!$salaryatt && !empty($employeeId) && !empty($rowDate)) {
                    $rowMonth = Carbon::parse($rowDate);
                    $salaryatt = EmployeeMonthlySalaryAttendance::where('employee_id', $employeeId)
                        ->whereMonth('for_month_of', $rowMonth->month)
                        ->whereYear('for_month_of', $rowMonth->year)
                        ->first();
                }

                if (!$salaryatt && !is_array($row) && !empty($date)) {
                    $rowMonth = Carbon::parse($date);
                    $salaryatt = EmployeeMonthlySalaryAttendance::where('employee_id', $row)
                        ->whereMonth('for_month_of', $rowMonth->month)
                        ->whereYear('for_month_of', $rowMonth->year)
                        ->first();
                }

                if (!$salaryatt) {
                    $errors[] = __('Attendance row not found for selected salary.');
                    continue;
                }

                $salaryMonth = Carbon::parse($rowDate ?: $salaryatt->for_month_of);

                $salary = EmployeeMonthlySalary::where('employee_id', $salaryatt->employee_id)
                    ->whereMonth('salary_date', $salaryMonth->month)
                    ->whereYear('salary_date', $salaryMonth->year)
                    ->first();

                if (!$salary) {
                    $errors[] = __('Salary not found for employee ID: ' . $salaryatt->employee_id);
                    continue;
                }

                if (trim(strtolower($salary->status ?? 'unpaid')) === 'paid') {
                    $errors[] = __('Paid salary cannot be finalized/unfinalized for employee ID: ' . $salaryatt->employee_id);
                    continue;
                }

                if ($action === 'unfinalize') {
                    $salary->sal_final = 0;
                    $salary->save();
                    $salaryatt->gm_final = 0;
                    $salaryatt->save();
                    $finalizedIds[] = $salary->id;
                    continue;
                }

                if ((int) $salaryatt->adm_final !== 1) {
                    $errors[] = __('Salary cannot be finalized before admin approval for employee ID: ' . $salaryatt->employee_id);
                    continue;
                }

                if ((int) $salary->on_hold === 1) {
                    $errors[] = __('Salary is on hold for employee ID: ' . $salaryatt->employee_id);
                    continue;
                }

                $salary->sal_final = 1;
                $salary->save();
                $salaryatt->gm_final = 1;
                $salaryatt->save();
                $finalizedIds[] = $salary->id;
            }
            \DB::commit();

            if (empty($finalizedIds)) {
                return response()->json(['success' => false, 'message' => implode(' ', $errors) ?: __('No salary was updated.')]);
            }

            $message = $action === 'unfinalize'
                ? __('Salary UnFinalized successfully.')
                : __('Salary Finalized successfully.');
            if (!empty($errors)) {
                $message .= ' ' . implode(' ', $errors);
            }

            return response()->json(['success' => true, 'message' => $message]);
        }   
        catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function salary_history(Request $request)
    {
        if (\Auth::user()->can('manage employee')) {
            if (\Auth::user()->type == 'Employee') {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = EmployeePayscaleDetail::with('scale', 'employee')
                    ->where('employee_id', '=', \Auth::user()->id)
                    ->whereHas('employee', function ($query) {
                        $query->where('owned_by', \Auth::user()->creatorId());
                    });
                // $query = Employee::where('user_id', '=', Auth::user()->id);
            } else if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $query = EmployeePayscaleDetail::with('scale', 'employee')
                    ->whereHas('employee', function ($query) {
                        $query->where('created_by', \Auth::user()->creatorId());
                    });
                // $query = Employee::with([
                //     'employee_payscale_details',
                //     'employee_payscale_details.scale'
                // ])->where('created_by', \Auth::user()->creatorId());
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = EmployeePayscaleDetail::with('scale', 'employee')
                    ->whereHas('employee', function ($query) {
                        $query->where('owned_by', \Auth::user()->ownedId());
                    });

                // $query = Employee::with(
                //     [
                //         'employee_payscale_details',
                //         'employee_payscale_details.scale'
                //     ]
                // )->where('owned_by', \Auth::user()->ownedId());
            }
            // dd($query->get());
            // Apply filters
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }

            // Check if the user requested Excel download
            if ($request->has('export') && $request->export == 'excel') {
                $employees = $query->get();  // Get all employees for Excel export
                return Excel::download(new SalaryHistoryExport($employees), 'salary_history.xlsx');
            }
            // dd($query->get());
            // Normal view logic for web

            $employeesscale = $query->orderByDesc('id')->get();
            // dd($employeesscale); 
            return view('employee.emp_salary_detail.history', compact('employeesscale', 'branches'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function salary_history(Request $request)
    // {

    //     if (\Auth::user()->can('manage employee')) {
    //         if (\Auth::user()->type == 'Employee') {
    //             $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
    //             $branches->prepend('Select Branch', '');
    //             $query = Employee::where('user_id', '=', Auth::user()->id);
    //         } else if (\Auth::user()->type == 'company') {
    //             $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
    //             $branches->prepend(\Auth::user()->name, \Auth::user()->id);
    //             $branches->prepend('Select Branch', '');
    //             $query = Employee::with([
    //                 'employee_payscale_details',
    //                 'employee_payscale_details.scale'
    //             ])->where('created_by', \Auth::user()->creatorId());
    //         } else {
    //             $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
    //             $branches->prepend('Select Branch', '');
    //             $query = Employee::with(
    //                 [
    //                     'employee_payscale_details',
    //                     'employee_payscale_details.scale'
    //                 ]
    //             )->where('owned_by', \Auth::user()->ownedId());
    //         }
    //         if (!empty($request->branches)) {
    //             $query->where('owned_by', '=', $request->branches);
    //         }
    //         $employees = $query->paginate(10);
    //         return view('employee.emp_salary_detail.history', compact('employees', 'branches'));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }
    public function salary_history_report(Request $request)
    {
        // dd($request->all());
        if (\Auth::user()->can('manage employee')) {
            $filterApplied = false;
            $branches = collect();
            $employees = collect();
            $departments = collect();
            $designations = collect();
            $employee = collect();
            $dateFrom = $request->input('from_date');
            $dateTo = $request->input('to_date');

            if (\Auth::user()->type == 'Employee') {
                $branches = User::where('id', \Auth::user()->ownedId())->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = Employee::where('user_id', \Auth::user()->id);
            } elseif (\Auth::user()->type == 'company') {
                $branches = User::where('type', 'branch')->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $employees = Employee::where('created_by', \Auth::user()->creatorId())
                    ->pluck('name', 'id')
                    ->prepend('Select Employee', '');
                $departments = Department::where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
                $departments->prepend('All', 'all');
                $designations = Designation::where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
                $designations->prepend('All', 'all');
                $query = Employee::with([
                    'employee_monthly_salaries' => function ($query) use ($dateFrom, $dateTo) {
                        if ($dateFrom && $dateTo) {
                            $fromDate = Carbon::parse($dateFrom)->startOfDay();
                            $toDate = Carbon::parse($dateTo)->endOfDay();
                            $query->whereBetween('salary_date', [$fromDate, $toDate]);
                        }
                    },
                    'employee_monthly_salaries_attend' => function ($query) use ($dateFrom, $dateTo) {
                        if ($dateFrom && $dateTo) {
                            $fromDate = Carbon::parse($dateFrom)->startOfDay();
                            $toDate = Carbon::parse($dateTo)->endOfDay();
                            $query->whereBetween('for_month_of', [$fromDate, $toDate]);
                        }
                    },
                    'employee_monthly_salaries.salaryheads.SalaryHead',
                    'employee_payscale_details'
                ])->where('created_by', \Auth::user()->creatorId());
            } else {
                $branches = User::where('id', \Auth::user()->ownedId())->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $employees = Employee::where('owned_by', \Auth::user()->ownedId())
                    ->pluck('name', 'id')
                    ->prepend('Select Employee', '');
                $departments = Department::where('owned_by', \Auth::user()->ownedId())->pluck('name', 'id');
                $departments->prepend('All', 'all');
                $designations = Designation::where('owned_by', \Auth::user()->ownedId())->pluck('name', 'id');
                $designations->prepend('All', 'all');
                $query = Employee::with([
                    'employee_monthly_salaries' => function ($query) use ($dateFrom, $dateTo) {
                        if ($dateFrom && $dateTo) {
                            $fromDate = Carbon::parse($dateFrom)->startOfDay();
                            $toDate = Carbon::parse($dateTo)->endOfDay();
                            $query->whereBetween('salary_date', [$fromDate, $toDate]);
                        }
                    },
                    'employee_monthly_salaries_attend' => function ($query) use ($dateFrom, $dateTo) {
                        if ($dateFrom && $dateTo) {
                            $fromDate = Carbon::parse($dateFrom)->startOfDay();
                            $toDate = Carbon::parse($dateTo)->endOfDay();
                            $query->whereBetween('for_month_of', [$fromDate, $toDate]);
                        }
                    },
                    'employee_monthly_salaries.salaryheads.SalaryHead',
                    'employee_payscale_details'
                ])->where('owned_by', \Auth::user()->ownedId());
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', $request->branches);
                $filterApplied = true;
            }
            if (!empty($request->employee_id)) {
                $query->where('id', $request->employee_id);
                $filterApplied = true;
            }
            if (!empty($request->designation_id) && $request->designation_id != 'all') {
                $query->where('designation_id', $request->designation_id);
                $filterApplied = true;
            }
            if (!empty($request->department_id) && $request->department_id != 'all') {
                $query->where('department_id', $request->department_id);
                $filterApplied = true;
            }
            if ($request->has('export') && $request->export == 'excel') {
                $employee = $query->get();
                return Excel::download(new SalaryHistoryReportExport($employee), 'salary_history_report.xlsx');
            }
            if ($request->has('export') && $request->export == 'pdf') {
                $employee = $query->get();
                return Excel::download(new SalaryHistoryReportExport($employee), 'salary_history_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
            }
            if ($filterApplied) {
                $employee = $query->get();
            }
            if ($request->filled('is_print') && $request->is_print == 1) {
                $employeesquery = $query->get();
                $bodyHtml = view('employee.reports.employee_sal_history_Pdf', compact('employee', 'branches'))->render();
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
                    ' . $headerHtml . '
                    <div class="footer">' . $footerHtml . '</div>
                    ' . $bodyHtml . '
                </body></html>';
                // dd($finalHtml);

                $options = new Options();
                $options->set('isHtml5ParserEnabled', true);
                $options->set('isRemoteEnabled', true);
                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($finalHtml);
                $dompdf->setPaper('A3', 'landscape');
                $dompdf->render();
                return $dompdf->stream('employee_sal_history_rpt.pdf', ['Attachment' => false]);
            }
            return view('employee.emp_salary_detail.emp_sal_his_report', compact('employee', 'branches', 'employees', 'departments', 'designations'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function deduction_sheet(Request $request)
    {
        $date = $request->input('date');
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches = $request->input('branches');
        $toDate = $date ? Carbon::parse($date)->endOfDay() : null;
        $fromDate = $date ? Carbon::parse($date)->subMonth()->day(25)->startOfDay() : null;
        $datas = collect();
        if ($date || $department_id || $designation_id || $branches) {
            if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
                $query = EmployeeMonthlySalary::with('employee', 'employee.designation')->where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $query = EmployeeMonthlySalary::with('employee', 'employee.designation')->where('on_hold', 0)->where('owned_by', '=', \Auth::user()->ownedId());
            }
            if ($date) {
                $query->whereYear('salary_date', $toDate->year)
                    ->whereMonth('salary_date', $toDate->month);
            }
            if ($department_id && $department_id != 'all') {
                $query->whereHas('employee', function ($query) use ($department_id) {
                    $query->where('department_id', $department_id);
                });
            }
            // dd($query->first());
            if ($branches) {
                $query->whereHas('employee', function ($query) use ($branches) {
                    $query->where('branch_id', $branches);
                });
            }
            if ($designation_id && $designation_id != 'all') {
                $query->whereHas('employee', function ($query) use ($designation_id) {
                    $query->where('designation_id', $designation_id);
                });
            }
            $datas = $query->get();
        }

        $viewData = [
            'datas' => $datas,
        ];
        $html = view('employee.emp_salary_detail.deduction_sheet', $viewData)->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        $html = '<html><head>
             <style>
                 @page {
                     margin-top: 100px;
                     margin-bottom: 100px;
                 }
                 .footer { position: fixed; bottom: -30px; height: 50px; left:0px; right:0px; }
             </style>
             </head><body>
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

    public function paymode_sheet(Request $request)
    {
        // dd($request->all());
        $date = $request->input('date');
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches = $request->input('branches');
        $paymode = $request->input('paymode');
        $toDate = $date ? Carbon::parse($date)->endOfDay() : null;
        $fromDate = $date ? Carbon::parse($date)->subMonth()->day(25)->startOfDay() : null;
        $datas = collect();
        if ($date || $department_id || $designation_id || $branches || $paymode) {
            if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
                $query = EmployeeMonthlySalary::with('employee', 'employee.designation', 'employee.employee_payscale_details', 'employee.employee_payscale_details.scale')
                    ->where('on_hold', 0)
                    ->where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $query = EmployeeMonthlySalary::with('employee', 'employee.designation', 'employee.employee_payscale_details', 'employee.employee_payscale_details.scale')
                    ->where('on_hold', 0)
                    ->where('owned_by', '=', \Auth::user()->ownedId());
            }
            if ($date) {
                $query->whereYear('salary_date', $toDate->year)
                    ->whereMonth('salary_date', $toDate->month);
            }
            if ($department_id && $department_id != 'all') {
                $query->whereHas('employee', function ($query) use ($department_id) {
                    $query->where('department_id', $department_id);
                });
            }
            if ($branches) {
                $query->whereHas('employee', function ($query) use ($branches) {
                    $query->where('branch_id', $branches);
                });
            }
            if ($designation_id && $designation_id != 'all') {
                $query->whereHas('employee', function ($query) use ($designation_id) {
                    $query->where('designation_id', $designation_id);
                });
            }
            if (!empty($paymode)) {
                    $query->where('paymode', $paymode);
            }
            $datas = $query->get();
        }

        $viewData = [
            'datas' => $datas,
            'requestdata' => $request->all(),
        ];
        $html = view('employee.emp_salary_detail.paymod_sheet', $viewData)->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        $html = '<html><head>
             <style>
                 @page {
                     margin-top: 100px;
                     margin-bottom: 100px;
                 }
                 .footer { position: fixed; bottom: -30px; height: 50px; left:0px; right:0px; }
             </style>
             </head><body>
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

    public function salary_sheet(Request $request)
    {
        $date = $request->input('date');
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches = $request->input('branches');
        $toDate = $date ? Carbon::parse($date)->endOfDay() : null;
        $fromDate = $date ? Carbon::parse($date)->subMonth()->day(25)->startOfDay() : null;
        $datas = collect();
        if ($date || $department_id || $designation_id || $branches) {
            if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
                $salaryHeads = SalaryHeads::where('created_by', '=', \Auth::user()->creatorId())->get();
                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $salaryHeads = SalaryHeads::where('owned_by', '=', \Auth::user()->ownedId())->get();
                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('owned_by', '=', \Auth::user()->ownedId());
            }

            if ($date) {
                $query->whereYear('salary_date', $toDate->year)
                    ->whereMonth('salary_date', $toDate->month);
            }
            if ($department_id && $department_id != 'all') {
                $query->whereHas('employee', function ($query) use ($department_id) {
                    $query->where('department_id', $department_id);
                });
            }
            if ($branches) {
                $query->whereHas('employee', function ($query) use ($branches) {
                    $query->where('branch_id', $branches);
                });
            }
            if ($designation_id && $designation_id != 'all') {
                $query->whereHas('employee', function ($query) use ($designation_id) {
                    $query->where('designation_id', $designation_id);
                });
            }
            $datas = $query->orderBy('department_id')->get();
        }

        $viewData = [
            'salaryHeads' => $salaryHeads,
            'datas' => $datas,
            'requestdata' => $request->all(),
        ];
        $html = view('employee.emp_salary_detail.salary_sheet', $viewData)->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        $html = '<html><head>
        <style>
            @page {
                margin-top: 100px;
                margin-bottom: 100px;
            }
            .footer { position: fixed; bottom: -30px; height: 50px; left:0px; right:0px; }
        </style>
        </head><body>
        <div class="footer">' . $footerHtml . '</div>
        ' . $html . '
        </body></html>';
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A3', 'Landscape');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);

        return response()->json(['base64Pdf' => $base64Pdf]);
    }
    public function gross_sheet(Request $request)
    {
        $date = $request->input('date');
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches = $request->input('branches');
        $toDate = $date ? Carbon::parse($date)->endOfDay() : null;
        $fromDate = $date ? Carbon::parse($date)->subMonth()->day(25)->startOfDay() : null;
        $datas = collect();
        if ($date || $department_id || $designation_id || $branches) {
            if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
                $salaryHeads = SalaryHeads::where('created_by', '=', \Auth::user()->creatorId())->get();
                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('on_hold', 0)->where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $salaryHeads = SalaryHeads::where('owned_by', '=', \Auth::user()->ownedId())->get();
                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('on_hold', 0)->where('owned_by', '=', \Auth::user()->ownedId());
            }

            if ($date) {
                $query->whereYear('salary_date', $toDate->year)
                    ->whereMonth('salary_date', $toDate->month);
            }
            if ($department_id && $department_id != 'all') {
                $query->whereHas('employee', function ($query) use ($department_id) {
                    $query->where('department_id', $department_id);
                });
            }
            if ($branches) {
                $query->whereHas('employee', function ($query) use ($branches) {
                    $query->where('branch_id', $branches);
                });
            }
            if ($designation_id && $designation_id != 'all') {
                $query->whereHas('employee', function ($query) use ($designation_id) {
                    $query->where('designation_id', $designation_id);
                });
            }
            $datas = $query->get();
        }

        $viewData = [
            'salaryHeads' => $salaryHeads,
            'datas' => $datas,
            'requestdata' => $request->all(),
        ];
        $html = view('employee.emp_salary_detail.gross_sheet', $viewData)->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        $html = '<html><head>
             <style>
                 @page {
                     margin-top: 100px;
                     margin-bottom: 100px;
                 }
                 .footer { position: fixed; bottom: -30px; height: 50px; left:0px; right:0px; }
             </style>
             </head><body>
             <div class="footer">' . $footerHtml . '</div>
             ' . $html . '
             </body></html>';
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'Portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);

        return response()->json(['base64Pdf' => $base64Pdf]);
    }
    public function advance(Request $request)
    {
        $date = $request->input('date');
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches = $request->input('branches');
        $toDate = $date ? Carbon::parse($date)->endOfDay() : null;
        $fromDate = $date ? Carbon::parse($date)->subMonth()->day(25)->startOfDay() : null;
        $datas = collect();
        if ($date || $department_id || $designation_id || $branches) {
            if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
                $salaryHeads = SalaryHeads::where('created_by', '=', \Auth::user()->creatorId())->get();
                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('on_hold', 0)->where('status', 'paid')->where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $salaryHeads = SalaryHeads::where('owned_by', '=', \Auth::user()->ownedId())->get();
                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('on_hold', 0)->where('status', 'paid')->where('owned_by', '=', \Auth::user()->ownedId());
            }

            if ($date) {
                $query->whereYear('salary_date', $toDate->year)
                    ->whereMonth('salary_date', $toDate->month);
            }
            if ($department_id && $department_id != 'all') {
                $query->whereHas('employee', function ($query) use ($department_id) {
                    $query->where('department_id', $department_id);
                });
            }
            if ($branches) {
                $query->whereHas('employee', function ($query) use ($branches) {
                    $query->where('branch_id', $branches);
                });
            }
            if ($designation_id && $designation_id != 'all') {
                $query->whereHas('employee', function ($query) use ($designation_id) {
                    $query->where('designation_id', $designation_id);
                });
            }
            $datas = $query->get();
        }

        $viewData = [
            'salaryHeads' => $salaryHeads,
            'datas' => $datas,
            'requestdata' => $request->all(),
        ];
        $html = view('employee.emp_salary_detail.advance', $viewData)->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        $html = '<html><head>
             <style>
                 @page {
                     margin-top: 100px;
                     margin-bottom: 100px;
                 }
                 .footer { position: fixed; bottom: -30px; height: 50px; left:0px; right:0px; }
             </style>
             </head><body>
             <div class="footer">' . $footerHtml . '</div>
             ' . $html . '
             </body></html>';
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'Portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);

        return response()->json(['base64Pdf' => $base64Pdf]);
    }
    public function salary_slip(Request $request)
    {
        $date           = $request->input('date');
        $department_id  = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches       = $request->input('branches');
        $exportType     = $request->input('export_type', 'pdf');
    
        $toDate   = $date ? \Carbon\Carbon::parse($date)->endOfDay() : null;
        $fromDate = $date ? \Carbon\Carbon::parse($date)->subMonth()->day(25)->startOfDay() : null;
    
        // 1) Primary: employee_ids[]
        $employeeIds = collect($request->input('employee_ids', []))
            ->filter()
            ->map(fn($v) => (int) $v)
            ->values()
            ->all();
    
        // 2) Fallback: legacy "checked" contained row ids (EmployeeMonthlySalary.id)
        if (empty($employeeIds) && $request->filled('checked')) {
            $rowIds = collect($request->input('checked', []))
                ->filter()
                ->map(fn($v) => (int) $v)
                ->values()
                ->all();
    
            if (!empty($rowIds)) {
                $rowQ = \App\Models\EmployeeMonthlySalary::query();
                if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
                    $rowQ->where('created_by', \Auth::user()->creatorId());
                } else {
                    $rowQ->where('owned_by', \Auth::user()->ownedId());
                }
                $employeeIds = $rowQ->whereIn('id', $rowIds)->pluck('employee_id')->unique()->values()->all();
            }
        }
    
        $datas = collect();
    
        if ($date || $department_id || $designation_id || $branches || !empty($employeeIds)) {
            if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
                $salaryHeads = \App\Models\SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
                $query = \App\Models\EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($q) use ($toDate) {
                        if ($toDate) {
                            $q->whereYear('for_month_of', $toDate->year)
                              ->whereMonth('for_month_of', $toDate->month);
                        }
                    },
                ])->where('created_by', \Auth::user()->creatorId());
            } else {
                $salaryHeads = \App\Models\SalaryHeads::where('owned_by', \Auth::user()->ownedId())->get();
                $query = \App\Models\EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($q) use ($toDate) {
                        if ($toDate) {
                            $q->whereYear('for_month_of', $toDate->year)
                              ->whereMonth('for_month_of', $toDate->month);
                        }
                    },
                ])->where('on_hold', 0)
                  ->where('status', 'unpaid')
                  ->where('owned_by', \Auth::user()->ownedId());
            }
    
            $employeeIds = collect($request->input('employee_ids', []))
            ->filter()->map(fn($v) => (int)$v)->values()->all();
        
        if ($toDate) {
            $query->whereYear('salary_date', $toDate->year)
                  ->whereMonth('salary_date', $toDate->month);
        }
        
        if (!empty($employeeIds)) {
            $query->whereIn('employee_id', $employeeIds);
        }
    
            if ($department_id && $department_id !== 'all') {
                $query->whereHas('employee', fn($q) => $q->where('department_id', $department_id));
            }
    
            if ($branches) {
                $query->whereHas('employee', fn($q) => $q->where('branch_id', $branches));
            }
    
            if ($designation_id && $designation_id !== 'all') {
                $query->whereHas('employee', fn($q) => $q->where('designation_id', $designation_id));
            }
    
            $datas = $query->get();
        } else {
            $salaryHeads = collect();
        }
    
        $viewData = [
            'salaryHeads' => $salaryHeads ?? [],
            'datas'       => $datas,
            'requestdata' => $request->all(),
        ];
    
        if ($exportType === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SalarySlipExport($viewData), 'salary_slip.xlsx');
        }
    
        // PDF (unchanged)
        $html = view('employee.emp_salary_detail.salary_slip', $viewData)->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        $html = '<html><head>
            <style>
                @page { margin-top: 100px; margin-bottom: 100px; }
                .footer { position: fixed; bottom: -30px; height: 50px; left:0; right:0; }
            </style>
        </head><body>
          <div class="footer">'.$footerHtml.'</div>'.$html.'
        </body></html>';
    
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'Portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
    
        return response()->json(['base64Pdf' => base64_encode($pdfContent)]);
    }

    public function salary_history_detail($id)
    {
        if (\Auth::user()->can('manage employee')) {
            if (\Auth::user()->type == 'Employee') {
                $query = Employee::where('user_id', '=', Auth::user()->id);
            } else if (\Auth::user()->type == 'company') {
                $query = Employee::with([
                    'employee_payscale_details',
                    'employee_payscale_details.scale'
                ])->where('id', $id)->where('created_by', \Auth::user()->creatorId());
            } else {
                $query = Employee::with(
                    [
                        'employee_payscale_details' => function ($query) {
                            $query->latest()->first();
                        },
                        'employee_payscale_details.scale'
                    ]
                )->where('id', $id)->where('owned_by', \Auth::user()->ownedId());
            }
            $employee = $query->first();
            $lastPayscaleDetail = $employee->employee_payscale_details->last();
            if (!$lastPayscaleDetail) {
                return redirect()->back()->with('error', __('Scale Not attached . please attach payscale First.'));
            }
            return view('employee.emp_salary_detail.salary_history_detail', compact('employee'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function generate_appointment_letter(Request $request, $id)
    {
        // dd($id);
        $empscale = EmployeePayscaleDetail::with('employee', 'scale')->where('id', $id)->first();
        $data = $request->all();

        // $employee = Employee::with([
        //     'employee_payscale_details',
        //     'designation',
        //     'employee_payscale_details.scale'
        // ])->where('id', $id)->first();
        // $lastPayscaleDetail = $employee->employee_payscale_details->last();
        if (\Auth::user()->type == 'company') {
            $appointmentletterdata = AppointmentLetter::where('id', $empscale->appletter)->where('created_by', \Auth::user()->creatorId())->first();
        } else {
            $appointmentletterdata = AppointmentLetter::where('id', $empscale->appletter)->where('owned_by', \Auth::user()->ownedId())->first();
        }
        // dd($appointmentletterdata);
        if (!$appointmentletterdata) {
            return response()->json(['error' => 'No appointment letter found. Please create an appointment letter first.'], 404);
        }
        if (!$empscale) {
            return response()->json(['error' => 'Scale Not attached . please attach payscale First.'], 404);
        }
        // dd($appointmentletterdata,$lastPayscaleDetail->appletter);
        $data['employee'] = $empscale->employee;
        $data['appointmentletterdata'] = $appointmentletterdata;
        $data['lastPayscaleDetail'] = $empscale;
        // dd($employee);
        $html = view('employee.emp_salary_detail.emp-appointment-letter', $data)->render();
        $headerHtml = view('employee.emp_salary_detail.pdf.header')->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
         $html = '<html><head>
             <style>
                 @page {
                     margin-top: 100px;
                     margin-bottom: 100px;
                 }
                 .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
                 .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; text-align: center; font-size: 10px; color: #888; }
             </style>
             </head><body>
             ' . $headerHtml . '
             <div class="footer">' . $footerHtml . '</div>
             ' . $html . '
             </body></html>';
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('enable_php', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);
    }

    public function generate_anexture(Request $request, $id)
    {
        $data = $request->all();
        // Load all necessary relationships
        $employee = Employee::with([
            'employee_payscale_details',
            'designation',
            'department',
            'branch',
            'user',
            'userbranch',
            'employee_payscale_details.scale.employeeScaleHeads.salaryHeads',
        ])->where('id', $id)->first();
        $data['employee'] = $employee;
        $lastPayscaleDetail = $employee->employee_payscale_details->last();
        $data['lastPayscaleDetail'] = $lastPayscaleDetail;
        // Load SchoolDetails for the employee's branch
        $branches_school = null;
        $branchUser = null;
        if ($employee && $employee->branch_id) {
            $branches_school = \App\Models\SchoolDetails::where('branch_id', $employee->branch_id)->first();
            $branchUser = \App\Models\User::where('id', $employee->branch_id)->first();
        }
        $data['branches_school'] = $branches_school;
        $data['branchUser'] = $branchUser;
        if (!$lastPayscaleDetail) {
            return response()->json(['error' => 'Scale Not attached . please attach payscale First.'], 404);
        }
        $headerHtml = view('employee.emp_salary_detail.pdf.headerForAnnexture')->render();

        $footerHtml = view('employee.emp_salary_detail.pdf.footerForAnnexture', $data)->render();
        $html = view('employee.emp_salary_detail.anexture', $data)->render();
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
             ' . $headerHtml . '
             <div class="footer">' . $footerHtml . '</div>
             ' . $html . '
             </body></html>';
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('enable_php', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);
    }

    public function printsalarydetail(Request $request)
    {
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches = $request->input('branches');
        $query = Employee::with([
            'employee_payscale_details',
            'employee_payscale_details.scale'
        ])->where('created_by', \Auth::user()->creatorId());
        $employees = $query->get();
        $viewData = [
            'employees' => $employees,
            'requestdata' => $request->all(),
        ];
        $html = view('employee.emp_salary_detail.printsalarydetail', $viewData)->render();
        $headerHtml = view('employee.emp_salary_detail.pdf.header')->render();
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
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);

        return response()->json(['base64Pdf' => $base64Pdf]);
    }
    /*
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
    public function hold_unhold(Request $request)
    {
        $ids = $request->input('employee_ids');
        $date = $request->input('date');
        $month = date('m', strtotime($date));
        $year = date('Y', strtotime($date));

        if (!$ids) {
            return response()->json(['success' => false, 'message' => __('No entries selected for hold/unhold.')]);
        }
        $errors = [];
        \DB::beginTransaction();

        try {
            foreach ($ids as $id) {
                $salary = EmployeeMonthlySalary::where('employee_id', $id)
                    ->whereMonth('salary_date', $month)
                    ->whereYear('salary_date', $year)
                    ->first();
                //    dd($salary);
                if ($salary) {
                    $salary->on_hold == 1 ? $salary->on_hold = 0 : $salary->on_hold = 1;
                    $salary->save();
                } else {
                    $errors[] = __('Employee not found with ID: ' . $id);
                }
            }
            \DB::commit();
            return response()->json(['success' => true, 'message' => __('Hold/Unhold status updated successfully.')]);
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('employee_ids');
        $date = $request->input('date');
        $month = date('m', strtotime($date));
        $year = date('Y', strtotime($date));

        if (!$ids) {
            return response()->json(['success' => false, 'message' => __('No entries selected for deletion.')]);
        }

        $deleteMessages = [];
        $errors = [];
        $deletedIds = [];

        \DB::beginTransaction();

        try {
            foreach ($ids as $id) {
                $salary = EmployeeMonthlySalary::where('employee_id', $id)
                    ->whereMonth('salary_date', $month)
                    ->whereYear('salary_date', $year)
                    ->where('status', '!=', 'paid')
                    ->first();

                if ($salary) {
                    if ($salary->on_hold == 1) {
                        $errors[] = __('Salary On_Hold u can not delete: ' . $id);
                        continue;
                    }
                    $salaryheads = EmployeeMonthlySalaryHeads::where('sal_id', $salary->id)->get();

                    foreach ($salaryheads as $salaryhead) {
                        $salaryhead->delete();
                    }

                    $deductionDetails = SalaryDeductionDetail::where('salary_id', $salary->id)->get();
                    foreach ($deductionDetails as $deductionDetail) {
                        if ($deductionDetail->type == 'loan' && !empty($deductionDetail->reference_id)) {
                            $loan = Loan::find($deductionDetail->reference_id);
                            if ($loan) {
                                $loanInstallment = LoanInstallment::where('salary_deduction_detail_id', $deductionDetail->id)
                                    ->orWhere(function ($query) use ($loan, $salary) {
                                        $query->where('loan_id', $loan->id)
                                            ->where('salary_id', $salary->id);
                                    })
                                    ->first();

                                if ($loanInstallment) {
                                    $loanInstallment->paid_amount = max(0, (float) $loanInstallment->paid_amount - (float) $deductionDetail->amount);
                                    $loanInstallment->status = 0;
                                    $loanInstallment->salary_id = null;
                                    $loanInstallment->salary_deduction_detail_id = null;
                                    $loanInstallment->paid_at = null;
                                    $loanInstallment->save();
                                }

                                $loan->received_amount = max(0, (float) $loan->received_amount - (float) $deductionDetail->amount);
                                $loan->save();
                            }
                        }

                        if ($deductionDetail->type == 'advance' && !empty($deductionDetail->reference_id)) {
                            EmployeeAdvance::where('id', $deductionDetail->reference_id)
                                ->where('deducted_salary_id', $salary->id)
                                ->update(['deducted_salary_id' => null]);
                        }

                        $deductionDetail->delete();
                    }

                    if ($salary->voucher_id) {
                        JournalEntry::where('id', $salary->voucher_id)->delete();
                        JournalItem::where('journal', $salary->voucher_id)->delete();
                    }
                    $salary->delete();

                    EmployeeMonthlySalaryAttendance::where('employee_id', $id)
                        ->whereYear('for_month_of', $year)
                        ->whereMonth('for_month_of', $month)
                        ->update(['sal_final' => 0]);
                    $deletedIds[] = $id;
                } else {
                    $errors[] = __('Salary not found for employee ID: ' . $id);
                    continue;
                }
            }

            if (count($deletedIds) > 0) {
                $deleteMessages[] = count($deletedIds) . ' ' . __('Salaries Rollbacked successfully.');
            }

            \DB::commit();

            $response = [
                'success' => true,
                'message' => implode(' ', $deleteMessages) ?: __('No entries deleted.'),
            ];

            if (!empty($errors)) {
                $response['errors'] = $errors;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => __('An error occurred during deletion: ') . $e->getMessage(),
            ]);
        }
    }



    //  Excel sheets

 public function export_salary_sheet(Request $request)
    {
        $date = $request->input('date');
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches = $request->input('branches');
        $toDate = $date ? Carbon::parse($date)->endOfDay() : null;
        $fromDate = $date ? Carbon::parse($date)->subMonth()->day(25)->startOfDay() : null;
        $datas = collect();
        if ($date || $department_id || $designation_id || $branches) {

            if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
                $salaryHeads = SalaryHeads::where('created_by', '=', \Auth::user()->creatorId())->get();

                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $salaryHeads = SalaryHeads::where('owned_by', '=', \Auth::user()->ownedId())->get();
                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('owned_by', '=', \Auth::user()->ownedId());
            }

            if ($date) {
                $query->whereYear('salary_date', $toDate->year)
                    ->whereMonth('salary_date', $toDate->month);
            }
            if ($department_id && $department_id != 'all') {
                $query->whereHas('employee', function ($query) use ($department_id) {
                    $query->where('department_id', $department_id);
                });
            }
            if ($branches) {
                // $query->whereHas('employee', function ($query) use ($branches) {
                        $query->where('owned_by', $branches);
                    // });
            }
            if ($designation_id && $designation_id != 'all') {
                $query->whereHas('employee', function ($query) use ($designation_id) {
                    $query->where('designation_id', $designation_id);
                });
            }
            $datas = $query->orderBy('department_id')->get();
        } else {
            return redirect()->back()->with('error', __('Search please.'));
        }
        $requestdata = $request->all();
        // dd($salaryHeads);
        // $viewData = [
        //     'salaryHeads' => @$salaryHeads,
        //     'datas' => $datas,
        //     'requestdata' => $request->all(),
        // ];
        $selectedMonth = $requestdata['date'];
        // just take month selectedMonth
        $monthSelected = date('m', strtotime($selectedMonth));
        $requestdata['month'] = $monthSelected;
        return Excel::download(new SalarySheetExport($salaryHeads, $datas, $requestdata), 'salary_sheet.xlsx');

    }

    public function export_deduction_sheet(Request $request)
    {
        $date = $request->input('date');
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches = $request->input('branches');
        $toDate = $date ? Carbon::parse($date)->endOfDay() : null;
        $fromDate = $date ? Carbon::parse($date)->subMonth()->day(25)->startOfDay() : null;
        $datas = collect();
        if ($date || $department_id || $designation_id || $branches) {

            if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
                $salaryHeads = SalaryHeads::where('created_by', '=', \Auth::user()->creatorId())->get();

                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $salaryHeads = SalaryHeads::where('owned_by', '=', \Auth::user()->ownedId())->get();
                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('owned_by', '=', \Auth::user()->ownedId());
            }

            if ($date) {
                $query->whereYear('salary_date', $toDate->year)
                    ->whereMonth('salary_date', $toDate->month);
            }
            if ($department_id && $department_id != 'all') {
                $query->whereHas('employee', function ($query) use ($department_id) {
                    $query->where('department_id', $department_id);
                });
            }
            if ($branches) {
                // $query->whereHas('employee', function ($query) use ($branches) {
                        $query->where('owned_by', $branches);
                    // });
            }
            if ($designation_id && $designation_id != 'all') {
                $query->whereHas('employee', function ($query) use ($designation_id) {
                    $query->where('designation_id', $designation_id);
                });
            }
            $datas = $query->orderBy('department_id')->get();
        } else {
            return redirect()->back()->with('error', __('Search please.'));
        }
        $requestdata = $request->all();
        $selectedMonthYear = $request->input('date');

        // $viewData = [
        //     'salaryHeads' => @$salaryHeads,
        //     'datas' => $datas,
        //     'requestdata' => $request->all(),
        // ];
        $selectedMonth = date('F Y', strtotime($selectedMonthYear));
        // just take month selectedMonth
        $monthSelected = date('m', strtotime($selectedMonth));
        $requestdata['month'] = $monthSelected;
        return Excel::download(new DeductionSheetExport($salaryHeads, $datas, $requestdata), 'deduction_sheet.xlsx');
        return Excel::download(new DeductionSheetExport($datas, $selectedMonthYear), 'deduction.xlsx');

        // $html = view('employee.emp_salary_detail.deduction_sheet', $viewData)->render();
        // $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        // $html = '<html><head>
        //      <style>
        //          @page {
        //              margin-top: 100px;
        //              margin-bottom: 100px;
        //          }
        //          .footer { position: fixed; bottom: -30px; height: 50px; left:0px; right:0px; }
        //      </style>
        //      </head><body>
        //      <div class="footer">' . $footerHtml . '</div>
        //      ' . $html . '
        //      </body></html>';
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'portrait');
        // $dompdf->render();
        // $pdfContent = $dompdf->output();
        // $base64Pdf = base64_encode($pdfContent);

        // return response()->json(['base64Pdf' => $base64Pdf]);
    }

    public function export_gross_sheet(Request $request)
    {
        // dd($request->all(),$request->input('designation_id'));
        $date = $request->input('date');
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches = $request->input('branches');
        $toDate = $date ? Carbon::parse($date)->endOfDay() : null;
        $fromDate = $date ? Carbon::parse($date)->subMonth()->day(25)->startOfDay() : null;
        $datas = collect();
        if ($date || $department_id || $designation_id || $branches) {
            if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
                $salaryHeads = SalaryHeads::where('created_by', '=', \Auth::user()->creatorId())->get();
                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('on_hold', 0)->where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $salaryHeads = SalaryHeads::where('owned_by', '=', \Auth::user()->ownedId())->get();
                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('on_hold', 0)->where('owned_by', '=', \Auth::user()->ownedId());
            }

            if ($date) {
                $query->whereYear('salary_date', $toDate->year)
                    ->whereMonth('salary_date', $toDate->month);
            }
            if ($department_id && $department_id != 'all') {
                $query->whereHas('employee', function ($query) use ($department_id) {
                    $query->where('department_id', $department_id);
                });
            }
            if ($branches) {
                $query->whereHas('employee', function ($query) use ($branches) {
                    $query->where('branch_id', $branches);
                });
            }
            if ($designation_id && $designation_id != 'all') {
                $query->whereHas('employee', function ($query) use ($designation_id) {
                    $query->where('designation_id', $designation_id);
                });
            }
            $datas = $query->get();
        }

        $requestdata = $request->all();

        return Excel::download(new GrossSheetExport(
            $salaryHeads,
            $datas
            ,
            $requestdata
        ), 'gross.xlsx');

    }

    public function export_paymode_sheet(Request $request)
    {
        // return redirect()->back()->with('error', __('Work in Progress'));
        $date = $request->input('date');
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $paymode = $request->input('paymode');
        $branches = $request->input('branches');
        $toDate = $date ? Carbon::parse($date)->endOfDay() : null;
        $fromDate = $date ? Carbon::parse($date)->subMonth()->day(25)->startOfDay() : null;
        $datas = collect();
        if ($date || $department_id || $designation_id || $branches) {
            if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
                $query = EmployeeMonthlySalary::with('employee', 'employee.designation', 'employee.employee_payscale_details', 'employee.employee_payscale_details.scale')->where('on_hold', 0)->where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $query = EmployeeMonthlySalary::with('employee', 'employee.designation', 'employee.employee_payscale_details', 'employee.employee_payscale_details.scale')->where('on_hold', 0)->where('owned_by', '=', \Auth::user()->ownedId());
            }

            if ($date) {
                $query->whereYear('salary_date', $toDate->year)
                    ->whereMonth('salary_date', $toDate->month);
            }
            if ($department_id && $department_id != 'all') {
                $query->whereHas('employee', function ($query) use ($department_id) {
                    $query->where('department_id', $department_id);
                });
            }
            if ($branches) {
                $query->whereHas('employee', function ($query) use ($branches) {
                    $query->where('branch_id', $branches);
                });
            }
            if ($designation_id && $designation_id != 'all') {
                $query->whereHas('employee', function ($query) use ($designation_id) {
                    $query->where('designation_id', $designation_id);
                });
            }
            if (!empty($paymode)) {
                $query->where('paymode', $paymode);
            }
            $datas = $query->get();
        }

        $requestdata = $request->all();
        return Excel::download(new PaymodesSheetExport($datas, $requestdata), 'paymode_sheet.xlsx');
    }

    public function export_advance_sheet(Request $request)
    {
        // return redirect()->back()->with('error', __('Work in Progress'));
        $date = $request->input('date');
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches = $request->input('branches');
        $toDate = $date ? Carbon::parse($date)->endOfDay() : null;
        $fromDate = $date ? Carbon::parse($date)->subMonth()->day(25)->startOfDay() : null;
        $datas = collect();
        if ($date || $department_id || $designation_id || $branches) {
            if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
                $salaryHeads = SalaryHeads::where('created_by', '=', \Auth::user()->creatorId())->get();
                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('on_hold', 0)->where('status', 'paid')->where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $salaryHeads = SalaryHeads::where('owned_by', '=', \Auth::user()->ownedId())->get();
                $query = EmployeeMonthlySalary::with([
                    'employee',
                    'employee.designation',
                    'salary_heads',
                    'employee.employee_payscale_details',
                    'employee.employee_payscale_details.scale',
                    'employee.employee_monthly_salaries_attend' => function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($query) use ($fromDate, $toDate) {
                            $query->whereYear('for_month_of', $toDate->year)
                                ->whereMonth('for_month_of', $toDate->month);
                        });
                    },
                ])->where('on_hold', 0)->where('status', 'paid')->where('owned_by', '=', \Auth::user()->ownedId());
            }

            if ($date) {
                $query->whereYear('salary_date', $toDate->year)
                    ->whereMonth('salary_date', $toDate->month);
            }
            if ($department_id && $department_id != 'all') {
                $query->whereHas('employee', function ($query) use ($department_id) {
                    $query->where('department_id', $department_id);
                });
            }
            if ($branches) {
                $query->whereHas('employee', function ($query) use ($branches) {
                    $query->where('branch_id', $branches);
                });
            }
            if ($designation_id && $designation_id != 'all') {
                $query->whereHas('employee', function ($query) use ($designation_id) {
                    $query->where('designation_id', $designation_id);
                });
            }
            $datas = $query->get();
        }
        $requestdata = $request->all();

        return Excel::download(new AdvanceSheetExport($salaryHeads, $datas, $requestdata), 'advance_sheet.xlsx');
    }

}

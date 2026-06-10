<?php

namespace App\Http\Controllers;

use App\Exports\SalaryAttendanceExport;
use App\Exports\SalarySheetExport;
use App\Models\AdvanceTaxCollection;
use App\Models\BankAccount;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeLeaves;
use App\Models\EmployeeMonthlySalary;
use App\Models\EmployeeMonthlySalaryAttendance as ModelsEmployeeMonthlySalaryAttendance;
use App\Models\EmployeeMonthlySalaryHeads;
use App\Models\EmployeePayscaleDetail;
use App\Models\Loan;
use App\Models\SalaryHeads;
use App\Models\SalaryDeductionDetail;
use App\Models\TaxSlab;
use App\Models\SalaryPayment;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeMonthlySalaryAttendance extends Controller
{
    private function isCashPaymode($paymode)
    {
        return strtolower(trim((string) $paymode)) === 'cash';
    }

    private function isTaxableSalaryHead($headName)
    {
        return !in_array(strtolower(trim((string) $headName)), [
            'medical',
            'medical allowance',
        ], true);
    }

    private function normalizeMonthDate($value)
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value)) {
            return $value . '-01';
        }

        return $value;
    }

    private function normalizeRequestMonthDate(Request $request): ?string
    {
        $date = $this->normalizeMonthDate($request->input('date'));

        if ($date) {
            $date = Carbon::parse($date)->startOfMonth()->format('Y-m-d');
            $request->merge(['date' => $date]);
        }

        return $date;
    }

    private function approvedAdvanceTaxCollection($employeeId, Carbon $fromDate, Carbon $toDate)
    {
        return (float) AdvanceTaxCollection::where('employee_id', $employeeId)
            ->where('status', 1)
            ->whereBetween('tax_month', [
                $fromDate->copy()->startOfMonth()->format('Y-m-d'),
                $toDate->copy()->endOfMonth()->format('Y-m-d'),
            ])
            ->sum('amount');
    }

    private function firstSalaryEobiAmounts($employee, $lastPayscaleDetail, Carbon $salaryDate, $workingDays, $monthDays): array
    {
        $employeeEobi = (float) ($lastPayscaleDetail->eobi ?? 0);
        $employerEobi = (float) ($lastPayscaleDetail->eobi_employer ?? 0);
        $monthDays = (float) $monthDays;
        $workingDays = (float) $workingDays;

        if (empty($employee->company_doj) || $monthDays <= 0) {
            return [
                'employee' => round($employeeEobi),
                'employer' => round($employerEobi),
            ];
        }

        $joiningDate = Carbon::parse($employee->company_doj);
        $salaryMonthStart = $salaryDate->copy()->startOfMonth();
        $salaryMonthEnd = $salaryDate->copy()->endOfMonth();

        $hasPreviousSalary = EmployeeMonthlySalary::where('employee_id', $employee->id)
            ->whereDate('salary_date', '<', $salaryMonthStart->format('Y-m-d'))
            ->exists();

        $isJoiningMonthFirstSalary = !$hasPreviousSalary && $joiningDate->between($salaryMonthStart, $salaryMonthEnd);

        if (!$isJoiningMonthFirstSalary) {
            return [
                'employee' => round($employeeEobi),
                'employer' => round($employerEobi),
            ];
        }

        $joiningMonthDays = min($monthDays, $joiningDate->copy()->startOfDay()->diffInDays($salaryMonthEnd) + 1);
        $eligibleDays = min($workingDays, $joiningMonthDays);

        if ($eligibleDays >= $monthDays) {
            return [
                'employee' => round($employeeEobi),
                'employer' => round($employerEobi),
            ];
        }

        $ratio = max(0, min(1, $eligibleDays / $monthDays));

        return [
            'employee' => round($employeeEobi * $ratio),
            'employer' => round($employerEobi * $ratio),
        ];
    }

    private function salaryAttendanceQuery(Request $request)
    {
        $date = $this->normalizeRequestMonthDate($request);
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches = $request->input('branches');
        $toDate = $date ? Carbon::parse($date)->endOfDay() : now()->endOfMonth();

        $query = ModelsEmployeeMonthlySalaryAttendance::with([
            'employee',
            'employee.department',
            'employee.designation',
        ])
            ->whereYear('for_month_of', $toDate->year)
            ->whereMonth('for_month_of', $toDate->month);

        if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
            $query->where('created_by', '=', \Auth::user()->creatorId());
        } else {
            $query->where('owned_by', '=', \Auth::user()->ownedId());
        }

        if ($department_id && $department_id != 'all') {
            $query->whereHas('employee', function ($query) use ($department_id) {
                $query->where('department_id', $department_id);
            });
        }

        if ($branches && $branches != null) {
            $query->where('owned_by', $branches);
        }

        if ($designation_id && $designation_id != 'all') {
            $query->whereHas('employee', function ($query) use ($designation_id) {
                $query->where('designation_id', $designation_id);
            });
        }

        return $query;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $date = $this->normalizeRequestMonthDate($request);
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches = $request->input('branches');
        $toDate = $date ? Carbon::parse($date)->endOfDay() : now()->endOfMonth();
        $fromDate = $date ? Carbon::parse($date)->subMonth()->day(25)->startOfDay() : now()->startOfMonth();
        if (\Auth::user()->type == 'Employee') {
            $branchesList = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branchesList->prepend('Select Branch', '');
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('All', 'all');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('All', 'all');
        } elseif (\Auth::user()->type == 'company') {
            $branchesList = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branchesList->prepend(\Auth::user()->name, \Auth::user()->id);
            $branchesList->prepend('Select Branch', '');
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('All', 'all');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('All', 'all');
        } else {
            $branchesList = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('All', 'all');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('All', 'all');
        }
        $datas = $this->salaryAttendanceQuery($request)
            ->get()
            ->sortBy(fn($row) => optional($row->employee)->name)
            ->values();

        return view('employee.monthly_salary_attendance.index', compact('branchesList', 'date', 'datas', 'designations', 'departments'));
    }

    public function export_salary_attendance(Request $request)
    {
        $this->normalizeRequestMonthDate($request);
        $reportType = $request->input('report_type') === 'details' ? 'details' : 'summary';
        $datas = $this->salaryAttendanceQuery($request)->get();

        if ($request->input('export_type') === 'pdf') {
            return Excel::download(
                new SalaryAttendanceExport($datas, $request->all(), $reportType),
                'salary_attendance_' . $reportType . '.pdf',
                \Maatwebsite\Excel\Excel::MPDF
            );
        }

        return Excel::download(
            new SalaryAttendanceExport($datas, $request->all(), $reportType),
            'salary_attendance_' . $reportType . '.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
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
        $date = $this->normalizeRequestMonthDate($request);
        if (!$date) {
            return response()->json(['success' => false, 'message' => __('Please select month.')]);
        }
        $inputDate = Carbon::parse($date);
        $day = $inputDate->day;
        $base = Carbon::parse($date);

        $fromDate = $base->copy()->day(25);

        if ($base->day < 25) {
            $fromDate->subMonth();
        }

        $toDate = $fromDate->copy()->addMonth()->day(25)->endOfDay();
        if ($day >= 25) {
            // current month 25th → next month 25th
            $fromDate = $inputDate->copy()->day(25);
            $toDate   = $inputDate->copy()->addMonth()->day(25);
        } else {
            // previous month 25th → current month 25th
            $fromDate = $inputDate->copy()->subMonth()->day(25);
            $toDate   = $inputDate->copy()->day(25);
        }

        $skippedEmployees = []; // <-- Collect skipped entries

        $query = Employee::with([
            'employee_leaves',
            'leaves' => function ($query) use ($fromDate, $toDate) {
                $query->where(function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween('start_date', [$fromDate, $toDate])
                        ->orWhereBetween('end_date', [$fromDate, $toDate])
                        ->orWhere(function ($query) use ($fromDate, $toDate) {
                            $query->where('start_date', '<', $fromDate)
                                ->where('end_date', '>', $toDate);
                        });
                });
            },
            'leaves.leaveType',
            'employee_rejoin'
        ])->where('is_res_ter', 0);

        // Filters...
        if ($request->department_id && $request->department_id != 'all') {
            $query->where('department_id', $request->department_id);
        }
        if ($request->branches) {
            $query->where('branch_id', $request->branches);
        }
        if ($request->designation_id && $request->designation_id != 'all') {
            $query->where('designation_id', $request->designation_id);
        }
        if (\Auth::user()->type == 'Employee') {
            $query->where('user_id', \Auth::user()->id);
        } elseif (\Auth::user()->type == 'company') {
            $query->where('created_by', \Auth::user()->creatorId());
        } else {
            $query->where('owned_by', \Auth::user()->ownedId());
        }

        $employees = $query->get();

        \DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $existingRecord = \App\Models\EmployeeMonthlySalaryAttendance::where('employee_id', $employee->id)
                    ->whereYear('for_month_of', $toDate->year)
                    ->whereMonth('for_month_of', $toDate->month)
                    ->first();

                if ($existingRecord) {
                    $skippedEmployees[] = [
                        'employee_id' => $employee->employee_id,
                        'reason' => 'Attendance already generated.'
                    ];
                    continue;
                }

                if ($employee->employee_leaves == null) {
                    $skippedEmployees[] = [
                        'employee_id' => $employee->employee_id,
                        'reason' => 'Leave structure not assigned.'
                    ];
                    continue;
                }

                $joiningDate = Carbon::parse($employee->company_doj);
                $diffInDays = 0;
                // if (strtolower($employee->department->name) == 'academic') {
                //     $probationEnd = $employee->probation_end ? Carbon::parse($employee->probation_end) : null;
                //     if ($probationEnd && $probationEnd->isFuture()) {
                //         $skippedEmployees[] = [
                //             'employee_id' => $employee->employee_id,
                //             'reason' => 'Academic employee still under probation.'
                //         ];
                //         continue;
                //     }
                // }
                $employeeScale = \App\Models\EmployeePayscaleDetail::where('employee_id', $employee->id)->orderByDesc('id')->first();
                if(!$employeeScale){
                    $skippedEmployees[] = [
                        'employee_id' => $employee->employee_id,
                        'reason' => 'No payscale defined.'
                    ];
                    continue; 
                }
                // $newda =carbon::date('Y-m-01',strtotime($inputDate));
                $newda =$inputDate->copy()->day(1);
                if ($joiningDate > $fromDate) {
                    if ($employeeScale && $employeeScale->effect_from) {
                        $effectFromDate = Carbon::parse($employeeScale->effect_from);
                        // dd($joiningDate, $fromDate, $effectFromDate);
                        // dd($effectFromDate->month ,$fromDate->month , $effectFromDate->year ,$fromDate->year,$newda,$fromDate,$effectFromDate);
                        if($effectFromDate->month == $fromDate->month && $effectFromDate->year == $fromDate->year){
                            $diffInDays = '0';
                        
                        }elseif ($effectFromDate->month == $newda->month && $effectFromDate->year == $newda->year) {
                            $diffInDays = $newda->diffInDays($effectFromDate, false);
                            // dd($diffInDays);
                            if ($diffInDays < 0 || $diffInDays > 31) {
                                // dd($diffInDays);
                                $skippedEmployees[] = [
                                    'employee_id' => $employee->employee_id,
                                    'reason' => 'Effect from date is invalid or exceeds standard working days (31).'
                                ];
                                continue;
                            }
                        } else {
                            $skippedEmployees[] = [
                                'employee_id' => $employee->employee_id,
                                'reason' => 'Effect from is in a different month than joining.'
                            ];
                            continue;
                        }
                    } else {
                        $skippedEmployees[] = [
                            'employee_id' => $employee->employee_id,
                            'reason' => 'No payscale defined.'
                        ];
                        continue;
                    }
                }

                // Calculate leave details
                $monthDays = $employeeScale->working_days ?? 30;
                $leaveDays = 0;
                $absentDays = 0;
                $annualLeaveConsumed = 0;
                $casualLeaveConsumed = 0;

                foreach ($employee->leaves as $leave) {
                    $leaveStart = Carbon::parse($leave->start_date);
                    $leaveEnd = Carbon::parse($leave->end_date);
                    $start = $leaveStart->greaterThan($fromDate) ? $leaveStart : $fromDate;
                    $end = $leaveEnd->lessThan($toDate) ? $leaveEnd : $toDate;
                    $days = $start->diffInDays($end) + 1;

                    if ($leave->leaveType->status == 'paid') {
                        $leaveDays += $days;
                    } else {
                        $absentDays += $days;
                    }

                    if (str_contains(strtolower($leave->leaveType->title), 'annual')) {
                        $annualLeaveConsumed += $days;
                    } elseif (str_contains(strtolower($leave->leaveType->title), 'casual')) {
                        $casualLeaveConsumed += $days;
                    }
                }
                // Save attendance
                $employeemonthlySlaryDetail = new \App\Models\EmployeeMonthlySalaryAttendance();
                $employeemonthlySlaryDetail->employee_id = $employee->id;
                $employeemonthlySlaryDetail->working_days = ($monthDays - $absentDays) - $diffInDays;
                $employeemonthlySlaryDetail->absents = $absentDays;
                // dd($diffInDays);
                $probationEnd = !empty($employee->probation_end) ? Carbon::parse($employee->probation_end) : null;

                if ($probationEnd == null || $probationEnd->isFuture()) {
                    $employeemonthlySlaryDetail->total_annual = 0;
                    $employeemonthlySlaryDetail->bal_annual = 0;
                } else {
                    $employeemonthlySlaryDetail->total_annual = $employee->employee_leaves->annual_total - $employee->employee_leaves->annual_consumed + $annualLeaveConsumed;
                    $employeemonthlySlaryDetail->bal_annual = $employee->employee_leaves->annual_total - $employee->employee_leaves->annual_consumed;
                }
                $employeemonthlySlaryDetail->total_casual = $employee->employee_leaves->casual_total - $employee->employee_leaves->casual_consumed + $casualLeaveConsumed;
                $employeemonthlySlaryDetail->bal_casual = $employee->employee_leaves->casual_total - $employee->employee_leaves->casual_consumed;
                $employeemonthlySlaryDetail->leave = $leaveDays;
                $employeemonthlySlaryDetail->month_days = $monthDays;
                $employeemonthlySlaryDetail->for_month_of = $date;
                $employeemonthlySlaryDetail->gm_final = 0;
                $employeemonthlySlaryDetail->sal_final = 0;
                $employeemonthlySlaryDetail->adm_final = 0;
                $employeemonthlySlaryDetail->lock_status = 0;
                $employeemonthlySlaryDetail->owned_by = $employee->owned_by;
                $employeemonthlySlaryDetail->created_by = \Auth::user()->creatorId();
                $employeemonthlySlaryDetail->save();
            }

            \DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Salary Attendance Generated Successfully.',
                'skipped' => $skippedEmployees
            ]);

        } catch (\Exception $e) {
            \DB::rollback();
            dd($e);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
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
        // dd($id);
        // $attendance = ModelsEmployeeMonthlySalaryAttendance::with([
        //     'employee',
        //     'employee.user',
        //     'employeemonthlysalary.salaryheads',
        //     'employeemonthlysalary.salaryheads.SalaryHead',
        // ])->where('adm_final', 1)->where('id', $id)->first();
        $employeesalary = EmployeeMonthlySalary::with('employee', 'employee.designation', 'employee.department', 'employee.employee_payscale_details')->where('id', $id)->first();
        if (empty($employeesalary)) {
            return redirect()->route('emp-month-sal-attendance.index')->with('error', 'Salary not found.')->withInput();
        }
        $attendanceMonth = Carbon::parse($employeesalary->salary_date)->month;
        $attendanceYear = Carbon::parse($employeesalary->salary_date)->year;
        $salaryAttendance = ModelsEmployeeMonthlySalaryAttendance::where('employee_id', $employeesalary->employee_id)
            ->whereMonth('for_month_of', $attendanceMonth)
            ->whereYear('for_month_of', $attendanceYear)
            ->first();
        $salaryEditable = trim(strtolower($employeesalary->status ?? 'unpaid')) === 'unpaid'
            && (int) optional($salaryAttendance)->gm_final !== 1;
        $arrears = \App\Models\EmployeeMonthlySalary::where('employee_id', $employeesalary->employee_id)->whereMonth('salary_date', '<', $attendanceMonth)
            ->where('status', 'unpaid')->get();
        return view('employee.monthly_salary_attendance.detail_monthly_salary', compact('employeesalary', 'arrears', 'salaryEditable'));
    }
    public function final_attendance(Request $request)
    {
        $date = $this->normalizeRequestMonthDate($request);
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $branches = $request->input('branches');
        $toDate = $date ? Carbon::parse($date)->endOfDay() : now()->endOfMonth();
        $fromDate = $date ? Carbon::parse($date)->subMonth()->day(25)->startOfDay() : now()->startOfMonth();
        if (\Auth::user()->type == 'Employee') {
            $branchesList = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branchesList->prepend('Select Branch', '');
            $departments = Department::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $departments->prepend('All', 'all');
            $designations = Designation::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $designations->prepend('All', 'all');
        } elseif (\Auth::user()->type == 'company') {
            $branchesList = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branchesList->prepend(\Auth::user()->name, \Auth::user()->id);
            $branchesList->prepend('Select Branch', '');
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('All', 'all');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('All', 'all');
        } else {
            $branchesList = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branchesList->prepend('Select Branch', '');
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('All', 'all');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('All', 'all');
        }
        // $datas = collect();
        if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
            $query = ModelsEmployeeMonthlySalaryAttendance::with('employee', 'employee.user')
                ->whereHas('employee', function ($query) {
                    $query->where('is_res_ter', 0);
                })
                ->whereYear('for_month_of', $toDate->year)
                ->whereMonth('for_month_of', $toDate->month)
                ->where('created_by', '=', \Auth::user()->creatorId());
        } else {
            $query = ModelsEmployeeMonthlySalaryAttendance::with('employee', 'employee.user')
                ->whereHas('employee', function ($query) {
                    $query->where('is_res_ter', 0);
                })->whereYear('for_month_of', $toDate->year)
                ->whereMonth('for_month_of', $toDate->month)
                ->where('owned_by', '=', \Auth::user()->ownedId());
        }
        if ($date || $department_id || $designation_id || $branches) {
            if ($date) {
                $query->whereYear('for_month_of', $toDate->year)->whereMonth('for_month_of', $toDate->month);
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
        }
        $datas = $query->get()
            ->sortBy(function ($attendance) {
                return strtolower(optional($attendance->employee)->name ?? '');
            })
            ->values();

        return view('employee.monthly_salary_attendance.final_attendance', compact('branchesList', 'date', 'datas', 'designations', 'departments'));

    }
    public function finalize_attendance(Request $request)
    {
        try {
            $rowIds = $request->input('rows');
            if (!$rowIds || !is_array($rowIds)) {
                return response()->json(['success' => false, 'message' => 'Invalid data provided.'], 400);
            }

            foreach ($rowIds as $id) {
                $attendance = ModelsEmployeeMonthlySalaryAttendance::find($id);
                if ($attendance) {
                    $attendance->accountant_finalize = 1;
                    $attendance->save();
                } else {
                    return response()->json(['success' => false, 'message' => 'Attendance record not found for ID: ' . $id], 404);
                }
            }
            return response()->json(['success' => true, 'message' => 'Attendance finalized.']);
        } catch (\Exception $e) {
            \Log::error('Error finalizing attendance: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while finalizing attendance.'], 500);
        }
    }
    public function finalize_adm_attendance(Request $request)
    {
        // dd($request->all());
        try {
            $rowIds = $request->input('rows');
            if (!$rowIds || !is_array($rowIds)) {
                return response()->json(['success' => false, 'message' => 'Invalid data provided.'], 400);
            }

            $attendances = ModelsEmployeeMonthlySalaryAttendance::with('employee')
                ->whereIn('id', $rowIds)
                ->get()
                ->keyBy('id');
            $errors = [];

            foreach ($rowIds as $id) {
                $attendance = $attendances->get($id);
                if (!$attendance) {
                    $errors[] = 'Attendance record not found for ID: ' . $id;
                    continue;
                }

                if ((int) $attendance->accountant_finalize !== 1) {
                    $employeeName = optional($attendance->employee)->name ?: $attendance->employee_id;
                    $errors[] = "Attendance is not accountant approved for {$employeeName}.";
                }
            }

            if (!empty($errors)) {
                return response()->json(['success' => false, 'message' => implode(' ', $errors)]);
            }

            foreach ($attendances as $attendance) {
                $attendance->adm_final = 1;
                $attendance->save();
            }
            return response()->json(['success' => true, 'message' => 'Attendance finalized And forwarded to Admin.']);
        } catch (\Exception $e) {
            \Log::error('Error finalizing attendance: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while finalizing attendance.'], 500);
        }
    }
    public function unfinalize_adm_attendance(Request $request)
    {
        // dd($request->all());
        try {
            $rowIds = $request->input('rows');
            if (!$rowIds || !is_array($rowIds)) {
                return response()->json(['error' => 'Invalid data provided.']);
            }

            foreach ($rowIds as $id) {
                $attendance = ModelsEmployeeMonthlySalaryAttendance::find($id);
                if (!$attendance) {
                    return response()->json(['error' => 'Attendance record not found for ID: ' . $id]);
                }

                $month = Carbon::parse($attendance->for_month_of)->month;
                $year = Carbon::parse($attendance->for_month_of)->year;
                $salary = EmployeeMonthlySalary::where('employee_id', $attendance->employee_id)
                    ->whereMonth('salary_date', $month)
                    ->whereYear('salary_date', $year)
                    ->first();
                if ($salary) {
                    $employeeName = optional($attendance->employee)->name ?: $attendance->employee_id;
                    $salaryMonth = Carbon::parse($salary->salary_date)->format('M-Y');
                    if (trim(strtolower($salary->status ?? 'unpaid')) === 'paid') {
                        return response()->json([
                            'error' => "Salary already paid for {$employeeName} ({$salaryMonth}), salary no {$salary->id}. Paid salary cannot be unfinalized.",
                        ]);
                    }

                    if ((int) $salary->sal_final === 1 || (int) $attendance->gm_final === 1) {
                        return response()->json([
                            'error' => "Salary is finalized for {$employeeName} ({$salaryMonth}), salary no {$salary->id}. Please unfinalize salary first.",
                        ]);
                    }
                }

                $attendance->accountant_finalize = 0;
                $attendance->adm_final = 0;
                $attendance->save();
            }
            return response()->json(['success' => true, 'message' => 'Attendance Unfinalized .']);
        } catch (\Exception $e) {
            // dd($e);
            \Log::error('Error finalizing attendance: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while unfinalizing attendance.']);
        }
    }
    public function month_salary(Request $request)
    {
        $date = $this->normalizeRequestMonthDate($request);
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $paymode = $request->input('paymode');
        $branches = $request->input('branches');
        if ($date) {
            $inputDate = Carbon::parse($date);
            $fromDate = $inputDate->copy()->startOfMonth();
            $toDate = $inputDate->copy()->endOfMonth();
        } else {

            $fromDate = now()->firstOfMonth();
            $toDate = now()->lastOfMonth();
            $date = $toDate->copy()->startOfMonth()->format('Y-m-d');
            $request->merge(['date' => $date]);
        }
             $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('All', 'all');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('All', 'all');
        // dd($fromDate,$toDate);
        if (\Auth::user()->type == 'Employee') {
            $branchesList = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branchesList->prepend('Select Branch', '');
            $salaryheads = SalaryHeads::where('owned_by', \Auth::user()->ownedId())->get();
        } elseif (\Auth::user()->type == 'company') {
            $branchesList = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branchesList->prepend(\Auth::user()->name, \Auth::user()->id);
            $branchesList->prepend('Select Branch', '');
            $salaryheads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
        } else {
            $branchesList = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branchesList->prepend('Select Branch', '');
            $salaryheads = SalaryHeads::where('owned_by', \Auth::user()->ownedId())->get();
        }
        // $datas = collect();
        if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
            $query = ModelsEmployeeMonthlySalaryAttendance::with([
                'employee.user',
                'employeemonthlysalary' => function ($query) use ($date) {
                    $toDate = Carbon::parse($date)->endOfDay();
                    $query->whereYear('salary_date', $toDate->year)
                        ->whereMonth('salary_date', $toDate->month);
                },
                'employeemonthlysalary.salaryheads' => function ($query) use ($date) {
                    $toDate = Carbon::parse($date)->endOfDay();
                    $query->whereYear('salary_date', $toDate->year)
                        ->whereMonth('salary_date', $toDate->month);
                },
                'employeemonthlysalary.salaryheads.SalaryHead',
                'employee.employee_payscale_details' => function ($query) {
                    $query->latest()->first();
                }
            ])
                // ->whereHas('employee', function ($query) {
                //     $query->where('is_res_ter', 0);
                // })
                ->whereYear('for_month_of', $toDate->year)
                ->whereMonth('for_month_of', $toDate->month)->where('created_by', '=', \Auth::user()->creatorId());
        } else {
            $query = ModelsEmployeeMonthlySalaryAttendance::with([
                'employee',
                'employee.user',
                'employeemonthlysalary' => function ($query) use ($date) {
                    $toDate = Carbon::parse($date)->endOfDay();
                    $query->whereYear('salary_date', $toDate->year)
                        ->whereMonth('salary_date', $toDate->month);
                },
                'employeemonthlysalary.salaryheads' => function ($query) use ($date) {
                    $toDate = Carbon::parse($date)->endOfDay();
                    $query->whereYear('salary_date', $toDate->year)
                        ->whereMonth('salary_date', $toDate->month);
                },
                'employeemonthlysalary.salaryheads.SalaryHead',
                'employee.employee_payscale_details' => function ($query) {
                    $query->latest()->first();
                }
            ])
                // ->whereHas('employee', function ($query) {
                //     $query->where('is_res_ter', 0);
                // })
                ->whereYear('for_month_of', $toDate->year)
                ->whereMonth('for_month_of', $toDate->month)->where('owned_by', '=', \Auth::user()->ownedId());
        }
        if ($date || $department_id || $designation_id || $branches) {
            if ($date) {
                $query->whereYear('for_month_of', $toDate->year)
                    ->whereMonth('for_month_of', $toDate->month);
            }
            if ($paymode && $paymode != 'all') {
                $query->whereHas('employeemonthlysalary', function ($query) use ($paymode) {
                    $query->where('paymode', $paymode);
                });
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
            // $query->where('employee_id',15);
            // return Excel::download(new UserDataExport($salaryHeads, $datas), 'payroll.xlsx');
            // return Excel::download(new SalarySheetExport($salaryHeads, $datas), 'payroll.xlsx');
        }
        $datas = $query->get();
        // dd($datas->last());
        return view('employee.monthly_salary_attendance.month_salary', compact('branchesList', 'salaryheads', 'date', 'datas', 'designations', 'departments'));

    }
    public function month_salary_generate(Request $request)
    {
        $date = $this->normalizeRequestMonthDate($request);
        $employee_ids = $request->input('employee_ids', []);
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');
        $paymodeFilter = $request->input('paymode');
        $branches = $request->input('branches');
       
        if (!$date) {
            return response()->json(['success' => false, 'message' => __('Please select month.')]);
        }

        $inputDate = Carbon::parse($date);
        $fromDate = $inputDate->copy()->startOfMonth();
        $toDate = $inputDate->copy()->endOfMonth();

        //previous month
        $previousMonth = $inputDate->copy()->subMonth();
        $previousMonthStart = $previousMonth->copy()->startOfMonth();
        $previousMonthEnd = $previousMonth->copy()->endOfMonth();

        if (empty($employee_ids)) {
            return response()->json(['success' => false, 'message' => 'No employees selected.']);
        }
        $dateObj = \Carbon\Carbon::parse($date);

        $currentMonth = $dateObj->month;
        $currentYear  = $dateObj->year;

        // remaining months in FY
        if ($currentMonth <= 6) {
            $months = 6 - $currentMonth + 1;
        } else {
            $months = (12 - $currentMonth + 1) + 6;
        }

        // tax year
        $taxYear = ($currentMonth <= 6)
            ? $currentYear - 1
            : $currentYear;

        // salary heads
        $salaryheads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();

        $query = ModelsEmployeeMonthlySalaryAttendance::with([
            'employee' => function ($query) {
                $query->where('is_res_ter', 0);
            },
            'employee.employee_loan',
            'employee.user',
            'employee.employee_transfers',
            'employee.employee_payscale_details'
        ])->where('adm_final', 1);
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
        if (!empty($paymodeFilter)) {
            $query->whereHas('employee.employee_payscale_details', function ($query) use ($paymodeFilter) {
                $query->where('id', function ($subquery) {
                    $subquery->select('id')
                        ->from('employee_payscale_details as epd')
                        ->last();
                })->where('paymode', $paymodeFilter);
            });
        }
        if (\Auth::user()->type == 'Employee' || \Auth::user()->type == 'company') {
            $query->where('created_by', '=', \Auth::user()->creatorId());
        } else {
            $query->where('owned_by', '=', \Auth::user()->ownedId());
        }

        $datas = $query->whereIn('employee_id', $employee_ids)
            ->whereYear('for_month_of', $toDate->year)
            ->whereMonth('for_month_of', $toDate->month)
            ->get();

        $alreadyGeneratedEmployees = [];

        \DB::beginTransaction();
        try {
            foreach ($datas as $data) {
                $existingSalary = EmployeeMonthlySalary::where('employee_id', $data->employee_id)
                    ->whereYear('salary_date', $toDate->year)
                    ->whereMonth('salary_date', $toDate->month)
                    ->where('owned_by', '=', $data->employee->owned_by)
                    ->first();
                if ($existingSalary) {
                    $alreadyGeneratedEmployees[] = $data->employee_id;
                    continue;
                }

                $transferDate = $data->employee->employee_transfers()->whereMonth('transfer_date', $toDate->month)
                    ->whereYear('transfer_date', $toDate->year)
                    ->first();

                if ($transferDate) {
                    $effectiveStartDate = Carbon::parse($transferDate->transfer_date);
                    $daysInPeriod = $effectiveStartDate->diffInDays($toDate->endOfMonth()) + 1;
                } else {
                    // dd($data);
                    $effectiveStartDate = $fromDate;
                    $daysInPeriod = $data->working_days;
                }

                $lastPayscaleDetail = $data->employee->employee_payscale_details->last();
                if (empty($lastPayscaleDetail)) {
                    continue;
                    // return response()->json(['success' => false, 'message' => 'Employee Dont Have payscale attached.']);
                } elseif (date('Y-m', strtotime($lastPayscaleDetail->effect_from)) > date('Y-m')) {
                    $lastPayscaleDetail = \App\Models\EmployeePayscaleDetail::where('employee_id', $data->employee_id)->where('id', '!=', $lastPayscaleDetail->id)->orderBy('id', 'Desc')->first();
                }
                $payscalesauto = \App\Models\EmployeeScale::with(
                    'employeeScaleHeads',
                    'employeeScaleHeads.SalaryHeads',
                    'employeepayScaledetailHeads'
                )->where('id', $lastPayscaleDetail->pay_scale_id)->first();

                $grossSalary = 0;
                $basicSalary = 0;
                $advanceAmount = 0;
                $advanceMonthStart = $toDate->copy()->startOfMonth();
                $advanceMonthEnd = $toDate->copy()->endOfMonth();
                $salaryAdvances = EmployeeAdvance::where('employee_id', $data->employee_id)
                    ->whereBetween('advance_date', [$advanceMonthStart, $advanceMonthEnd])
                    ->where('status', 1)
                    ->get();
                foreach ($salaryAdvances as $advance) {
                    $advanceAmount += $advance->advance_amount;
                }
                foreach ($payscalesauto->employeeScaleHeads as $scale_head) {
                    if ($scale_head->SalaryHeads->head == 'Initial Basic') {
                        $initialBasicHeadValue = $scale_head->head_value;
                        $basicSalary = round(($initialBasicHeadValue / $data->month_days) * $daysInPeriod);
                    } else {
                        $grossSalary += round((($scale_head->head_value / $data->month_days) * ($daysInPeriod)));
                    }
                }
                $addition = $lastPayscaleDetail->other_add + $lastPayscaleDetail->conv + $lastPayscaleDetail->drns + $lastPayscaleDetail->misc + $lastPayscaleDetail->chaild_concession;
                $taxableAddition = $lastPayscaleDetail->other_add + $lastPayscaleDetail->drns + $lastPayscaleDetail->misc;
                $grossSalary += $basicSalary + $addition;
                $eobiAmounts = $this->firstSalaryEobiAmounts(
                    $data->employee,
                    $lastPayscaleDetail,
                    $toDate,
                    $data->working_days,
                    $data->month_days
                );
                $employeeEobiAmount = $eobiAmounts['employee'];
                $employerEobiAmount = $eobiAmounts['employer'];

                $loanAmount = 0;
                $securityLoanAmount = 0;
                $salaryLoanDeductions = [];
                $toDateStartOfMonth = Carbon::parse($toDate)->startOfMonth();
                $toDateEndOfMonth = $toDateStartOfMonth->copy()->endOfMonth();
                $salaryLoans = Loan::where('employee_id', $data->employee_id)
                    ->with('installments')
                    ->where('status', 1)
                    ->whereDate('from_pay_month', '<=', $toDateEndOfMonth->format('Y-m-d'))
                    ->whereDate('loan_ended', '>=', $toDateStartOfMonth->format('Y-m-d'))
                    ->get();

                foreach ($salaryLoans as $loan) {
                    $loanStartDate = Carbon::parse($loan->from_pay_month)->startOfMonth();
                    $loanEndDate = Carbon::parse($loan->loan_ended)->endOfMonth();

                    if ($loan->status == 1 && $toDateStartOfMonth->between($loanStartDate, $loanEndDate) && !$loan->isStoppedForMonth($toDateStartOfMonth)) {
                        $remainingLoanAmount = max(0, (float) $loan->amount - (float) $loan->received_amount);
                        $installment = $loan->nextPayableInstallment($toDateStartOfMonth);
                        $installmentAmount = $installment
                            ? min((float) $installment->due_amount, $remainingLoanAmount)
                            : min((float) $loan->per_month_amount, $remainingLoanAmount);

                        if ($installmentAmount > 0 && $loan->emp_sec == 'security') {
                            $securityLoanAmount += $installmentAmount;
                        } elseif ($installmentAmount > 0) {
                            $loanAmount += $installmentAmount;
                        }

                        if ($installmentAmount > 0) {
                            $salaryLoanDeductions[] = [
                                'loan' => $loan,
                                'installment' => $installment,
                                'amount' => $installmentAmount,
                            ];
                        }
                    }
                }

                //tax calculate on basis of working days. 
                $monthTax = $this->calculateProratedTax(
                    employee_id:   $data->employee_id,
                    tax_year:      $taxYear,
                    date:          $date,
                    scaleHeads:    $payscalesauto->employeeScaleHeads,
                    otherAdds:     $taxableAddition,
                    workingDays:   $data->working_days,
                    monthDays:     $data->month_days,
                    months:        $months,
                    paymode:       $lastPayscaleDetail->paymode
                );
                if($monthTax == 'noslab'){
                    return response()->json(['success' => false, 'message' => 'Tax slab not found for the year '.$taxYear.'. Please contact admin.']);
                }
                // dd($monthTax, $data->employee_id, $taxYear, $daysInPeriod, $data->month_days, $payscalesauto->employeeScaleHeads, $addition);

                 $deduction = $loanAmount + $securityLoanAmount + $advanceAmount + $monthTax + $lastPayscaleDetail->emp_sec + $lastPayscaleDetail->pessi + $employeeEobiAmount + $lastPayscaleDetail->other_deduction;
                // dd($basicSalary, $grossSalary,$deduction);
                $net_sal = ($grossSalary  - $deduction) > 0 ? ($grossSalary  - $deduction) : 0;


                $employeemonthlysal = EmployeeMonthlySalary::create([
                    'employee_id' => $data->employee_id,
                    'department_id' => $data->employee->department_id,
                    'bank_from_id' => $lastPayscaleDetail->account_id,
                    'paymode' => $lastPayscaleDetail->paymode,
                    'account_number' => $lastPayscaleDetail->account_number,
                    'salary_date' => $date,
                    'scale_id' => $lastPayscaleDetail->pay_scale_id,
                    'scale_no' => $payscalesauto->scale_no,
                    'sal_days' => $data->working_days,
                    'basics' => $initialBasicHeadValue,
                    'conv' => $lastPayscaleDetail ? $lastPayscaleDetail->conv : '0',
                    'other_add' => $lastPayscaleDetail ? $lastPayscaleDetail->other_add : '0',
                    'chaild_con' => $lastPayscaleDetail ? $lastPayscaleDetail->chaild_concession : '0',
                    'drns' => $lastPayscaleDetail ? $lastPayscaleDetail->drns : '0',
                    'misc' => $lastPayscaleDetail ? $lastPayscaleDetail->misc : '0',
                    'stop_sal' => 0,
                    'other' => '0',
                    'gross' => round($grossSalary),
                    'loan' => $loanAmount ? $loanAmount : '0',
                    'emp_sec' => $lastPayscaleDetail ? $lastPayscaleDetail->emp_sec : '0',
                    'pessi_employer' => $lastPayscaleDetail ? $lastPayscaleDetail->pessi_employer : '0',
                    'pessi' => $lastPayscaleDetail ? $lastPayscaleDetail->pessi : '0',
                    'it' => $monthTax ? $monthTax : '0',
                    'eobi' => $employeeEobiAmount,
                    'eobi_employer' => $employerEobiAmount,
                    'dedu' => $lastPayscaleDetail ? round($lastPayscaleDetail->other_deduction) : '0',
				 	'emp_sec_loan' => $securityLoanAmount ? round($securityLoanAmount) : '0',
                    'tra_course' => 0,
                    'sal_advance' => $advanceAmount ? round($advanceAmount) : '0',
                    'prc_final' => 0,
                    'net_pay' => round($net_sal),
                    'sal_final' => 0,
                    'on_hold' => 0,
                    'owned_by' => $data->employee->owned_by,
                    'created_by' => \Auth::user()->creatorId(),
                ]);
                $created_date = date('Y-m-d', strtotime($date)).' '.date('H:i:s');
                $employeemonthlysal->created_at = $created_date ?? Carbon::now();
                $employeemonthlysal->created_at = $created_date ?? Carbon::now();
                $employeemonthlysal->save();

                foreach ($salaryLoanDeductions as $loanDeduction) {
                    $salaryLoan = $loanDeduction['loan'];
                    $loanInstallment = $loanDeduction['installment'];
                    $salaryLoanDeductionAmount = round($loanDeduction['amount']);
                    if ($salaryLoanDeductionAmount <= 0) {
                        continue;
                    }

                    $deductionDetail = SalaryDeductionDetail::create([
                        'salary_id' => $employeemonthlysal->id,
                        'employee_id' => $data->employee_id,
                        'type' => 'loan',
                        'sub_type' => $salaryLoan->emp_sec,
                        'reference_id' => $salaryLoan->id,
                        'amount' => round($salaryLoanDeductionAmount),
                        'note' => 'Loan installment deduction - ' . $salaryLoan->title,
                        'coa_id' => $salaryLoan->chartaccount_id,
                    ]);

                    if ($loanInstallment) {
                        $loanInstallment->paid_amount = min((float) $loanInstallment->amount, (float) $loanInstallment->paid_amount + $salaryLoanDeductionAmount);
                        if ((float) $loanInstallment->paid_amount >= (float) $loanInstallment->amount) {
                            $loanInstallment->status = 1;
                            $loanInstallment->paid_at = $employeemonthlysal->salary_date;
                        }
                        $loanInstallment->salary_id = $employeemonthlysal->id;
                        $loanInstallment->salary_deduction_detail_id = $deductionDetail->id;
                        $loanInstallment->save();
                    }

                    $salaryLoan->received_amount = ((float) $salaryLoan->received_amount) + round($salaryLoanDeductionAmount);
                    $salaryLoan->save();
                }

                foreach ($salaryAdvances as $advance) {
                    SalaryDeductionDetail::create([
                        'salary_id' => $employeemonthlysal->id,
                        'employee_id' => $data->employee_id,
                        'type' => 'advance',
                        'sub_type' => 'salary_advance',
                        'reference_id' => $advance->id,
                        'amount' => round($advance->advance_amount),
                        'note' => 'Salary advance deduction',
                        'coa_id' => $advance->chartaccount_id,
                    ]);

                    $advance->deducted_salary_id = $employeemonthlysal->id;
                    $advance->save();
                }

                // $newitems = [];
                $i = 0;
                foreach ($payscalesauto->employeeScaleHeads as $scale_head) {
                    $headValue = round((($scale_head->head_value / $data->month_days) * ($daysInPeriod)));
                    $salaryheadmonthly = EmployeeMonthlySalaryHeads::create([
                        'employee_id' => $data->employee_id,
                        'scale_id' => $employeemonthlysal->scale_id,
                        'scale_no' => $employeemonthlysal->scale_no,
                        'sal_id' => $employeemonthlysal->id,
                        'salary_date' => $date,
                        'head_id' => $scale_head->head,
                        'head_value' => $headValue,
                        'owned_by' => $employeemonthlysal->owned_by,
                        'created_by' => $employeemonthlysal->created_by,
                    ]);
                    $salaryheadmonthly->created_at = $created_date ?? Carbon::now();
                    $salaryheadmonthly->updated_at = $updated_date ?? Carbon::now();
                    $salaryheadmonthly->save();
                    // $newitems[$i]['prod_id'] = $salaryheadmonthly->id;
                    $i++;
                }
                $data->sal_final = 1;
                $data->save();

                if ($data) {
                    $allAccounts = [];
                    $deductionAccounts = $this->salaryDeductionVoucherAccounts($employeemonthlysal, $lastPayscaleDetail);
                    // dd($lastPayscaleDetail);
                    $allAccounts = [
                        [
                            'account_id' => 216,
                            'name' => 'Salary Expense (Basic + Med + Rent + Sec)',
                            'debit' =>
                                (float) ($grossSalary ?? 0),
                            'credit' => 0,
                        ],
                        // Dr: Employer PASSI
                        [
                            'account_id' => 258,
                            'name' => 'Salary Expense - Employer PASSI',
                            'debit' => round($employeemonthlysal->pessi_employer),
                            'credit' => 0,
                        ],
                        // Dr: Employer EOBI
                        [
                            'account_id' => 217,
                            'name' => 'Salary Expense - Employer EOBI',
                            'debit' => round($employeemonthlysal->eobi_employer),
                            'credit' => 0,
                        ],
                        [
                            'account_id' => $lastPayscaleDetail->security_receive_account,
                            'name' => 'Employee Security Payable',
                            'debit' => 0,
                            'credit' => round($employeemonthlysal->emp_sec),
                        ],
                        // Cr: Income Tax Payable
                        [
                            'account_id' => $lastPayscaleDetail->tax_payable_account,
                            'name' => 'Tax Payable (Income Tax)',
                            'debit' => 0,
                            'credit' => round($employeemonthlysal->it),
                        ],
                        // Cr: EOBI Payable (Employee)
                        [
                            'account_id' => $lastPayscaleDetail->eobi_payable_account,
                            'name' => 'EOBI Payable (Employee)',
                            'debit' => 0,
                            'credit' => round($employeemonthlysal->eobi),
                        ],
                        // Cr: PASSI Payable (Employee)
                        [
                            'account_id' => $lastPayscaleDetail->pessi_payable_account,
                            'name' => 'PASSI Payable (Employee)',
                            'debit' => 0,
                            'credit' => round($employeemonthlysal->pessi),
                        ],
                        [
                            'account_id' => $lastPayscaleDetail->other_dedu_payable_account,
                            'name' => 'Other Deduction Payable',
                            'debit' => 0,
                            'credit' => round($employeemonthlysal->dedu ?? 0), // or $all_data[?]
                        ],
                        // Cr: Net Salary Payable
                        [
                            'account_id' => $lastPayscaleDetail->net_payable_account,
                            'name' => 'Net Salary Payable',
                            'debit' => 0,
                            'credit' => round($employeemonthlysal->net_pay),
                        ],
                        // Cr: Employer PASSI Payable
                        [
                            // 'type' => 'Liabilities2',
                            // 'sub_type' => 'Payables29',
                            'account_id' => 225,
                            'name' => 'Employer PASSI Payable',
                            'debit' => 0,
                            'credit' => round($employeemonthlysal->pessi_employer),
                        ],
                        // Cr: Employer EOBI Payable
                        [
                            // 'type' => 'Liabilities2',
                            // 'sub_type' => 'Payables29',
                            'account_id' => 221,
                            'name' => 'Employer EOBI Payable',
                            'debit' => 0,
                            'credit' => round($employeemonthlysal->eobi_employer),
                        ],
                    ];
                    $allAccounts = array_merge($allAccounts, $deductionAccounts);
                    $filteredAccounts = array_filter($allAccounts, function ($item) {
                        return ($item['debit'] ?? 0) > 0 || ($item['credit'] ?? 0) > 0;
                    });
                    // dd($filteredAccounts);
                    $journal_data = [
                        'date' => $employeemonthlysal->salary_date,
                        'reference' => 'SAL-' . $employeemonthlysal->id,
                        'employee_name' => $data->employee->name,
                        'no' => $employeemonthlysal->id,
                        'salary_month' => date('F Y', strtotime($employeemonthlysal->salary_date)),
                        'id' => $employeemonthlysal->id,
                        'category' => 'salary',
                        'user_id' => $data->employee->user_id,
                        'user_type' => 'employee',
                        'owned_by' => $data->employee->owned_by,
                        'created_by' => $data->employee->created_by,
                        'accounts' => array_values($filteredAccounts),
                        'created_at' => $created_date ?? Carbon::now(),
                    ];
                    // dd($filteredAccounts,$journal_data);
                    $journal = Utility::Salaryjrentryvoucher($journal_data);
                    $employeemonthlysal->voucher_id = $journal;
                    $employeemonthlysal->save();
                }
            }
            \DB::commit();
            // dd($alreadyGeneratedEmployees);
            $message = 'Salary generated successfully.';
            if (!empty($alreadyGeneratedEmployees)) {
                $message .= ' However, salaries were already generated for the following employees: ' . implode(', ', $alreadyGeneratedEmployees);
            }
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            dd($e);
            \DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function calculateProratedTax(
        int $employee_id,
        int $tax_year,
        $date,
        $scaleHeads,
        float $otherAdds,
        int $workingDays,
        int $monthDays,
        int $months,
        $paymode = null
    ) {
        if ($this->isCashPaymode($paymode)) {
            return 0;
        }

        // -----------------------------
        // 1. FISCAL YEAR SETUP
        // -----------------------------
        $currentMonth = (int) date('n', strtotime($date));

        $fyStart = $currentMonth >= 7
            ? \Carbon\Carbon::create(date('Y'), 7, 1)
            : \Carbon\Carbon::create(date('Y') - 1, 7, 1);

        $fyEnd = \Carbon\Carbon::parse($date)->subMonth()->endOfMonth();

        // -----------------------------
        // 2. PREVIOUS SALARY (YTD)
        // -----------------------------
        $prevPaid = EmployeeMonthlySalary::with('scaleHeads.salaryHeads')
            ->where('employee_id', $employee_id)
            ->whereBetween('salary_date', [$fyStart, $fyEnd])
            ->where(function ($query) {
                $query->whereNull('paymode')
                    ->orWhereRaw('LOWER(TRIM(paymode)) != ?', ['cash']);
            })
            ->get();

        $prevTaxPaid = (float) $prevPaid->sum('it');
        $prevTaxPaid += $this->approvedAdvanceTaxCollection($employee_id, $fyStart, \Carbon\Carbon::parse($date));

        $prevOtherAdds = $prevPaid->sum(function ($s) {
            return
                ($s->misc ?? 0) +
                ($s->drns ?? 0) +
                ($s->other_add ?? 0) ;
        });
		$prevSalAmnt = 0;

        foreach ($prevPaid as $sal) {

                 foreach ($sal->scaleHeads as $head) {

                // foreach ($sal->salary_heads as $head) {
                //     if (
                //         $head->SalaryHead &&
                //         in_array($head->SalaryHead->head, ['Initial Basic', 'House Rent'])
                //     ) {
                //         $prevSalAmnt += $head->head_value;
                //     }
                // }
                 if (
                        $head->salaryHeads &&
                        $this->isTaxableSalaryHead($head->salaryHeads->head)
                    ) {
                        $prevSalAmnt += $head->head_value;
                    }
                }

                $prevSalAmnt +=
                    ($sal->misc ?? 0) +
                    ($sal->drns ?? 0) +
                    ($sal->other_add ?? 0);
            }

        // -----------------------------
        // 3. CURRENT SCALE SALARY
        // -----------------------------
        $monthlyBase = 0;

        foreach ($scaleHeads as $head) {
            if (
                $head->SalaryHeads &&
                $this->isTaxableSalaryHead($head->SalaryHeads->head)
            ) {
                $monthlyBase += $head->head_value;
            }
        }

        $monthlyBase += $otherAdds;

        // -----------------------------
        // 4. PROJECTED INCOME (IMPORTANT FIX)
        // -----------------------------
        $projectedIncome = $monthlyBase * $months;

        $yearlySal = $prevSalAmnt + $projectedIncome;
        // -----------------------------
        // 6. TAX YEAR
        // -----------------------------
        $currentYear = date('Y');
        if ($currentMonth <= 6) {
            $currentYear--;
        }

        // -----------------------------
        // 5. TAX SLAB
        // -----------------------------
        $slab = TaxSlab::where('year', $currentYear)
            ->where(function ($query) use ($yearlySal) {
                $query->where(function ($q) use ($yearlySal) {
                    $q->where('lower_limit', '<=', $yearlySal)
                        ->where('upper_limit', '>=', $yearlySal);
                })
                ->orWhere(function ($q) use ($yearlySal) {
                    $q->where('lower_limit', '<=', $yearlySal)
                        ->whereNull('upper_limit');
                });
            })
            ->first();

        if (!$slab) {
            return 0;
        }

        // -----------------------------
        // 6. TAX CALCULATION (CORRECT METHOD)
        // -----------------------------
        $taxableAmount = $yearlySal - ($slab->lower_limit - 1);

        $annualTax = ($taxableAmount * $slab->prev_limit_percentage) / 100
                    + $slab->fixed_tax_amount;

        $remainingTax = max($annualTax - $prevTaxPaid, 0);

        // -----------------------------
        // 7. MONTHLY TAX DISTRIBUTION
        // -----------------------------
        $monthlyTax = $months > 0 ? $remainingTax / $months : 0;

        $monthlyTax = max($monthlyTax, 0);

        // -----------------------------
        // 8. DAILY PRORATION (FINAL STEP)
        // -----------------------------
        if ($monthDays <= 0) {
            return 0;
        }

        // $dailyTax = $monthlyTax / $monthDays;

        // $finalTax = round($dailyTax * $workingDays);
        $finalTax = round($monthlyTax);
        return max($finalTax, 0);
    }
    // return response()->json([
    //     'success' => true,
    //     'message' => 'Monthly salaries generated successfully.'
    // ]);
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
        $emp_sal = EmployeeMonthlySalary::findOrFail($id);
        $salaryDate = Carbon::parse($emp_sal->salary_date);
        $salaryAttendance = ModelsEmployeeMonthlySalaryAttendance::where('employee_id', $emp_sal->employee_id)
            ->whereMonth('for_month_of', $salaryDate->month)
            ->whereYear('for_month_of', $salaryDate->year)
            ->first();

        if (trim(strtolower($emp_sal->status ?? 'unpaid')) !== 'unpaid') {
            return redirect()->back()->with('error', 'Paid salary can not be edited.');
        }

        if ((int) optional($salaryAttendance)->gm_final === 1) {
            return redirect()->back()->with('error', 'GM finalized salary can not be edited. Please unfinalize salary first.');
        }

        $emp_sal->conv = $request->conv ? $request->conv : '0';
        $emp_sal->chaild_con = $request->chaild_concession ? $request->chaild_concession : '0';
        $emp_sal->drns = $request->drns ? $request->drns : '0';
        $emp_sal->misc = $request->misc ? $request->misc : '0';
        $emp_sal->stop_sal = 0;
        $emp_sal->it = $request->itax ? $request->itax : '0';
        $emp_sal->dedu = $request->other_deduction ? $request->other_deduction : '0';
        $emp_sal->tra_course = 0;
        $emp_sal->sal_advance = $request->advance ? $request->advance : '0';
        $emp_sal->prc_final = 0;
        $emp_sal->net_pay = $request->net ? $request->net : '0';
        $emp_sal->save();
        return redirect()->back()->with('success', 'Employee Salary Updated Successfully !');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('rows');
        if (!$ids) {
            return response()->json(['success' => false, 'message' => __('No entries selected for deletion.')]);
        }
        $deleteMessages = [];
        $errors = [];
        $deletedIds = [];
        foreach ($ids as $id) {
            $attendance = ModelsEmployeeMonthlySalaryAttendance::find($id);
            if (!$attendance) {
                $errors[] = "Entry with ID $id not found.";
                continue;
            }
            if ($attendance->adm_final == 1) {
                $errors[] = "Entry with ID $id cannot be deleted because it's admin finalized.";
                continue;
            }
            $month = Carbon::parse($attendance->for_month_of)->month;
            $year = Carbon::parse($attendance->for_month_of)->year;
            $salary = EmployeeMonthlySalary::where('employee_id', $attendance->employee_id)
                ->whereMonth('salary_date', $month)
                ->whereYear('salary_date', $year)
                ->first();
            if ($salary) {
                $errors[] = "Salary already generated for $id attendance. Please delete the salary first.";
                continue;
            }
            $attendance->delete();
            $deletedIds[] = $id;
        }
        if (count($errors) > 0) {
            $message = __('Some entries could not be deleted: ') . implode(', ', $errors);
            return response()->json(['success' => false, 'message' => $message]);
        }
        return response()->json(['success' => true, 'message' => __('Selected attendances and salaries have been successfully deleted.')]);
    }


    public function payment(Request $request)
    {
        // if(\Auth::user()->can('create payment purchase'))
        // {
        // dd($request->all());
        $ids = $request->input('employee_ids');
        $date = $this->normalizeRequestMonthDate($request);
        if (!$date) {
            return response()->json(['success' => false, 'message' => __('Please select month.')]);
        }
        $month = date('m', strtotime($date));
        $year = date('Y', strtotime($date));

        if (!$ids) {
            return response()->json(['success' => false, 'message' => __('No entries selected for Paid.')]);
        }
        $errors = [];
        $paidIds = [];
        \DB::beginTransaction();

        try {
            foreach ($ids as $id) {
                $salary = EmployeeMonthlySalary::where('employee_id', $id)
                    ->whereMonth('salary_date', $month)
                    ->whereYear('salary_date', $year)
                    ->where('status', '!=', 'paid')
                    ->first();

                if (!$salary) {
                    $errors[] = __('Salary not found or already paid for employee ID: ' . $id);
                    continue;
                }

                $attendance = ModelsEmployeeMonthlySalaryAttendance::where('employee_id', $id)
                    ->whereMonth('for_month_of', $month)
                    ->whereYear('for_month_of', $year)
                    ->first();

                if (!$attendance || (int) $attendance->adm_final !== 1) {
                    $errors[] = "Salary not admin approved for employee with ID: " . $id;
                    continue;
                }

                if ($salary->sal_final == 0) {
                    $errors[] = "Salary not finalized for employee with ID: " . $id;
                    continue;
                }

                if ($salary->on_hold == 1) {
                    $errors[] = "Salary On_Hold u can not paid: " . $id;
                    continue;
                }

                if ($salary) {
                    //voucher 

                    $salarydetail = EmployeePayscaleDetail::where('employee_id', $id)->latest()->first();
                    if (!$salarydetail) {
                        $errors[] = "PayScale detail not found for employee with ID: " . $id;
                        continue;
                    }
                    $bank = BankAccount::where('id', $salarydetail->account_id)->first();
                    if (!$bank) {
                        $errors[] = "Bank account not found for employee with ID: " . $id;
                        continue;
                    } else if ($bank->chart_account_id == '' || $bank->chart_account_id == null) {
                        $errors[] = "Cash account not allowed for employee with ID: " . $id;
                        continue;
                    }
                    $payment = new SalaryPayment();
                    $payment->employee_id = $id;
                    $payment->salary_id = $salary->id;
                    $payment->net_pay = $salary->net_pay;
                    $payment->bank_id = $bank->id;
                    $payment->account_number = $salarydetail->account_number;
                    $payment->payment_method = $salarydetail->paymode;
                    $payment->reference = 'SAL-' . $salary->id;
                    $payment->description = 'Salary for ' . $salary->employee->name . ' (Salary ID: ' . $salary->id . ') for the month of ' . date('F Y', strtotime($salary->salary_date));
                    $payment->owned_by = $salary->employee->owned_by;
                    $payment->created_by = $salary->employee->created_by;
                    $payment->save();
                    // dd($bank);
                    $journal_data = [
                        'date' => $salary->salary_date,
                        'reference' => 'SAL-' . $salary->id,
                        'employee_name' => $salary->employee->name,
                        'no' => $salary->id,
                        'salary_month' => date('F Y', strtotime($salary->salary_date)),
                        'id' => $salary->id,
                        'category' => 'salary',
                        'user_id' => $salary->employee->user_id,
                        'user_type' => 'employee',
                        'owned_by' => $salary->employee->owned_by,
                        'created_by' => $salary->employee->created_by,

                        'accounts' => [
                            [
                                'account_id' => $bank->chart_account_id,
                                'name' => 'Bank Account Credit against salary ' . @$salary->id . ' of ' . @$salary->employee->name,
                                'debit' => 0,
                                'credit' => $salary->net_pay,
                            ],
                            [
                                'account_id' => $salarydetail->net_payable_account,
                                'name' => 'Net Salary Payable',
                                'debit' => $salary->net_pay,
                                'credit' => 0,
                            ],
                        ],
                    ];
                    if (strtolower($salarydetail->paymode) == 'cash' || strtolower($salarydetail->paymode) == 'cheque') {
                        //crv
                        $voucher = Utility::Salarycrvvoucher($journal_data); //crv
                    } else {
                        // brv
                        $voucher = Utility::Salarybrvvoucher($journal_data); //brv
                    }
                    $salary->status = 'paid';
                    $salary->paid_date = date('Y-m-d');
                    $salary->save();
                    $payment->journal_id = $voucher;
                    $payment->save();
                    $paidIds[] = $salary->id;
                }
            }

            if (empty($paidIds)) {
                \DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => implode(' ', $errors) ?: __('No salary was paid.'),
                ]);
            }

            \DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Salary Paid successfully.',
                'error' => $errors
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function salaryDeductionVoucherAccounts($salary, $lastPayscaleDetail): array
    {
        $details = SalaryDeductionDetail::where('salary_id', $salary->id)
            ->whereIn('type', ['loan', 'advance'])
            ->get();

        $accounts = [];
        foreach ($details as $detail) {
            $accountId = $detail->coa_id;
            if (!$accountId && $detail->type == 'loan' && $detail->sub_type == 'security') {
                $accountId = $lastPayscaleDetail->security_receive_account ?? null;
            } elseif (!$accountId && $detail->type == 'loan') {
                $accountId = $lastPayscaleDetail->other_dedu_payable_account ?? null;
            } elseif (!$accountId && $detail->type == 'advance') {
                $accountId = 216;
            }

            if (!$accountId) {
                continue;
            }

            if ($detail->type == 'advance') {
                $name = 'Advance Salary';
            } elseif ($detail->sub_type == 'security') {
                $name = 'Employee Security Payable';
            } else {
                $name = 'Loan Deduction Payable';
            }

            $key = $detail->type . '-' . $detail->sub_type . '-' . $accountId;
            if (!isset($accounts[$key])) {
                $accounts[$key] = [
                    'account_id' => $accountId,
                    'name' => $name,
                    'debit' => 0,
                    'credit' => 0,
                ];
            }

            $accounts[$key]['credit'] += round($detail->amount);
        }

        return array_values($accounts);
    }

    public function createPayment(Request $request, $purchase_id)
    {

        if (\Auth::user()->can('create payment purchase')) {
            \DB::beginTransaction();
            try {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'date' => 'required',
                        'amount' => 'required',
                        'account_id' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }
                $purchasePayment = new PurchasePayment();
                $purchasePayment->purchase_id = $purchase_id;
                $purchasePayment->date = $request->date;
                $purchasePayment->amount = $request->amount;
                $purchasePayment->account_id = $request->account_id;
                $purchasePayment->payment_method = 0;
                $purchasePayment->reference = $request->reference;
                $purchasePayment->description = $request->description;
                if (!empty($request->add_receipt)) {
                    $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                    $request->add_receipt->storeAs('uploads/payment', $fileName);
                    $purchasePayment->add_receipt = $fileName;
                }
                $purchasePayment->save();
                $purchase = Purchase::where('id', $purchase_id)->first();
                $due = $purchase->getDue();
                $total = $purchase->getTotal();

                if ($purchase->status == 0) {
                    $purchase->send_date = date('Y-m-d');
                    $purchase->save();
                }

                if ($due <= 0) {
                    $purchase->status = 4;
                    $purchase->save();
                } else {
                    $purchase->status = 3;
                    $purchase->save();
                }
                $purchasePayment->user_id = $purchase->vender_id;
                $purchasePayment->user_type = 'Vender';
                $purchasePayment->type = 'Partial';
                $purchasePayment->owned_by = \Auth::user()->id;
                $purchasePayment->created_by = \Auth::user()->id;
                $purchasePayment->payment_id = $purchasePayment->id;
                $purchasePayment->category = 'Bill';
                $purchasePayment->account = $request->account_id;
                Transaction::addTransaction($purchasePayment);

                $vender = Vender::where('id', $purchase->vender_id)->first();

                $payment = new PurchasePayment();
                $payment->name = $vender['name'];
                $payment->method = '-';
                $payment->date = \Auth::user()->dateFormat($request->date);
                $payment->amount = \Auth::user()->priceFormat($request->amount);
                $payment->bill = 'bill ' . \Auth::user()->purchaseNumberFormat($purchasePayment->purchase_id);

                Utility::userBalance('vendor', $purchase->vender_id, $request->amount, 'debit');

                Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');

                // Send Email
                // $setings = Utility::settings();
                // if($setings['new_bill_payment'] == 1)
                // {

                //     $vender = Vender::where('id', $purchase->vender_id)->first();
                //     $billPaymentArr = [
                //         'vender_name'   => $vender->name,
                //         'vender_email'  => $vender->email,
                //         'payment_name'  =>$payment->name,
                //         'payment_amount'=>$payment->amount,
                //         'payment_bill'  =>$payment->bill,
                //         'payment_date'  =>$payment->date,
                //         'payment_method'=>$payment->method,
                //         'company_name'=>$payment->method,

                //     ];

                //     $resp = Utility::sendEmailTemplate('new_bill_payment', [$vender->id => $vender->email], $billPaymentArr);

                //     return redirect()->back()->with('success', __('Payment successfully added.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));

                // }

                $bankAccount = BankAccount::find($request->account_id);
                $data['id'] = $purchasePayment->id;
                $data['date'] = $purchasePayment->date;
                $data['reference'] = $request->reference;
                $data['description'] = $purchasePayment->purchase_id;
                $data['prod_id'] = $purchasePayment->id;
                $data['amount'] = $purchasePayment->amount;
                $data['category'] = 'Purchase';
                $data['owned_by'] = $purchasePayment->created_by;
                $data['created_by'] = $purchasePayment->created_by;
                $data['account_id'] = $bankAccount->chart_account_id;
                if (strtolower($bankAccount->bank_name) == 'cash' || strtolower($bankAccount->holder_name) == 'cash') {
                    $dataret = Utility::cpv_entry($data);
                } else {
                    $dataret = Utility::bpv_entry($data);
                }
                \DB::commit();
                return redirect()->back()->with('success', __('Payment successfully added.'));

            } catch (\Exception $e) {
                \DB::rollback();
                dd($e);
                return redirect()->back()->with('error', 'something went wrong');
            }
        }

    }
}

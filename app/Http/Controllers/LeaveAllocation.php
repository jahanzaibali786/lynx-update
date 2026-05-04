<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLeaves;
use Illuminate\Http\Request;

class LeaveAllocation extends Controller
{
    public function index()
    {
        $emp_leave = EmployeeLeaves::join('employees', 'employees.id', '=', 'employee_leaves.employee_id')->where('employees.is_res_ter','0')
            ->orderBy('employees.name')->select('employees.id as emp_id','employees.employee_id as emp_no','employees.name as emp_name',('employee_leaves.*'))->get();

        return view('employee.leave.index', compact('emp_leave'));
    } 
    //edit
    public function edit($id)
    {
        $emp_leave = EmployeeLeaves::find($id);
        return view('employee.leave.edit', compact('emp_leave'));
    }  
    //update
    public function update(Request $request, $id)
    {
        $emp_leave = EmployeeLeaves::find($id);
        $emp_leave->casual_total = $request->casual_total;
        $emp_leave->casual_consumed = $request->casual_consumed;
        $emp_leave->annual_total = $request->annual_total;
        $emp_leave->annual_consumed = $request->annual_consumed;
        $emp_leave->save();
        return redirect()->route('emp-leaves.index')->with('success', 'Leave allocation updated successfully.');
    }
    
    public function assignLeavesToAll()
    {
        set_time_limit(0);

        $today = now();

        Employee::where('is_res_ter', 0)
            ->chunk(200, function ($employees) use ($today) {

                foreach ($employees as $employee) {

                    $today = now();

                    $joiningDate = \Carbon\Carbon::parse($employee->company_doj);
                    $probationEndDate = \Carbon\Carbon::parse($employee->probation_end);

                    // ✅ New logic
                    if ($joiningDate->year == $today->year) {
                        $effectiveDate = $joiningDate->startOfMonth();
                    } else {
                        $effectiveDate = $today->startOfMonth();
                    }

                    $annualTotal = 0;
                    $casualTotal = 0;

                    if ($employee->category == 'Regular') {

                        if (
                            $joiningDate->year < $today->year ||
                            $probationEndDate->year < $today->year
                        ) {
                            $annualTotal = 12 * 2.5;
                            $casualTotal = 12 * 0.8;

                        } elseif ($today->greaterThanOrEqualTo($probationEndDate)) {

                            $remainingAnnualMonths = 12 - $probationEndDate->month + 1;
                            $annualTotal = $remainingAnnualMonths * 2.5;

                            $remainingCasualMonths = 12 - $effectiveDate->month + 1;
                            $casualTotal = $remainingCasualMonths * 0.8;

                        } else {
                            $remainingCasualMonths = 12 - $effectiveDate->month + 1;
                            $casualTotal = $remainingCasualMonths * 0.8;
                        }
                    }
                  
                    $a=EmployeeLeaves::updateOrCreate(
                        ['employee_id' => $employee->id],
                        [
                            'annual_total' => $annualTotal,
                            'casual_total' => $casualTotal
                        ]
                    );
                    $a->save();
                }
            });

        return "Leaves assigned successfully - " . now();
    }
}

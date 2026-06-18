<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use LogsActions;
    protected $fillable = [
        'user_id',
        'salute',
        'name',
        'f_name',
        'cnic',
        'dob',
        'gender',
        'religion',
        'blood_group',
        'phone',
        'area',
        'address',
        'present_address',
        'category',
        'email',
        'password',
        'employee_id',
        'branch_id',
        'department_id',
        'designation_id',
        'security',
        'pessi',
        'pessi_employer',
        'eobi',
        'eobi_employer',
        'company_doj',
        'probation_period',
        'probation_end',
        'documents',
        'account_holder_name',
        'account_number',
        'bank_name',
        'bank_identifier_code',
        'branch_location',
        'tax_payer_id',
        'is_res_ter',
        'owned_by',
        'created_by',
    ];

    public function documents()
    {
        return $this->hasMany('App\Models\EmployeeDocument', 'employee_id', 'employee_id')->get();
    }
    public function finalsettlement()
    {
        return $this->hasOne('App\Models\EmployeeFinalSettlement', 'emp_id', 'id');
    }
    public function employee_payscale_details()
    {
        return $this->hasMany('App\Models\EmployeePayscaleDetail', 'employee_id', 'id');
    }
    public function employee_monthly_salaries()
    {
        return $this->hasMany('App\Models\EmployeeMonthlySalary', 'employee_id', 'id');
    }
    public function employee_monthly_salaries_attend()
    {
        return $this->hasMany('App\Models\EmployeeMonthlySalaryAttendance', 'employee_id', 'id');
    }
    public function employee_transfers()
    {
        return $this->hasMany(EmployeeTransfer::class);
    }
    public function employee_rejoin()
    {
        return $this->hasMany('App\Models\EmployeeRejoin', 'employee_id', 'id');
        // return $this->hasMany(EmployeeRejoin::class);
    }
    public function employee_loan()
    {
        return $this->belongsTo('App\Models\Loan', 'id', 'employee_id');
    }
    public function employee_leaves()
    {
        return $this->hasOne('App\Models\EmployeeLeaves', 'employee_id', 'id');
    }
    public function leaves()
    {
        return $this->hasMany('App\Models\Leave', 'employee_id', 'id');
    }
    public function salary_type()
    {
        return $this->hasOne('App\Models\PayslipType', 'id', 'salary_type')->pluck('name')->first();
    }

    public function master()
    {
        return $this->belongsTo(SchoolDetails::class, 'owned_by', 'branch_id');
    }

    public function headOffice()
    {
        return $this->belongsTo(SchoolDetails::class, 'created_by', 'branch_id');
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
    }

    public function getDesignationFormattedAttribute(): string
    {
           $text = $this->designation->name ?? '';

            if (strlen($text) <= 25) return $text;

            return str_replace("\n", "<br>", wordwrap($text, 30, "\n", false));
    }

    public function get_net_salary()
    {

        //allowance
        $allowances = Allowance::where('employee_id', '=', $this->id)->get();
        $total_allowance = 0;
        foreach ($allowances as $allowance) {
            if ($allowance->type == 'fixed') {
                $totalAllowances = $allowance->amount;
            } else {
                $totalAllowances = $allowance->amount * $this->salary / 100;
            }
            $total_allowance += $totalAllowances;
        }

        //commission
        $commissions = Commission::where('employee_id', '=', $this->id)->get();
        $total_commission = 0;
        foreach ($commissions as $commission) {
            if ($commission->type == 'fixed') {
                $totalCom = $commission->amount;
            } else {
                $totalCom = $commission->amount * $this->salary / 100;
            }
            $total_commission += $totalCom;
        }

        //Loan
        $loans = Loan::where('employee_id', '=', $this->id)->get();
        $total_loan = 0;
        foreach ($loans as $loan) {
            if ($loan->type == 'fixed') {
                $totalloan = $loan->amount;
            } else {
                $totalloan = $loan->amount * $this->salary / 100;
            }
            $total_loan += $totalloan;
        }


        //Saturation Deduction
        $saturation_deductions = SaturationDeduction::where('employee_id', '=', $this->id)->get();
        $total_saturation_deduction = 0;
        foreach ($saturation_deductions as $deductions) {
            if ($deductions->type == 'fixed') {
                $totaldeduction = $deductions->amount;
            } else {
                $totaldeduction = $deductions->amount * $this->salary / 100;
            }
            $total_saturation_deduction += $totaldeduction;
        }

        //OtherPayment
        $other_payments = OtherPayment::where('employee_id', '=', $this->id)->get();
        $total_other_payment = 0;
        $total_other_payment = 0;
        foreach ($other_payments as $otherPayment) {
            if ($otherPayment->type == 'fixed') {
                $totalother = $otherPayment->amount;
            } else {
                $totalother = $otherPayment->amount * $this->salary / 100;
            }
            $total_other_payment += $totalother;
        }

        //Overtime
        $over_times = Overtime::where('employee_id', '=', $this->id)->get();
        $total_over_time = 0;
        foreach ($over_times as $over_time) {
            $total_work = $over_time->number_of_days * $over_time->hours;
            $amount = $total_work * $over_time->rate;
            $total_over_time = $amount + $total_over_time;
        }


        //Net Salary Calculate
        $advance_salary = $total_allowance + $total_commission - $total_loan - $total_saturation_deduction + $total_other_payment + $total_over_time;

        $employee = Employee::where('id', '=', $this->id)->first();

        $net_salary = (!empty($employee->salary) ? $employee->salary : 0) + $advance_salary;

        return $net_salary;

    }

    public static function allowance($id)
    {

        //allowance
        $allowances = Allowance::where('employee_id', '=', $id)->get();
        $total_allowance = 0;
        foreach ($allowances as $allowance) {
            $total_allowance = $allowance->amount + $total_allowance;
        }

        $allowance_json = json_encode($allowances);

        return $allowance_json;

    }

    public static function last_scale($id)
    {

        $scale = EmployeePayscaleDetail::where('employee_id', '=', $id)->orderBy('desc')->first();
        return $scale;

    }

	    public function latestPayscale()
    {
        return $this->hasOne(EmployeePayscaleDetail::class)->latestOfMany();
    }

    public static function commission($id)
    {
        //commission
        $commissions = Commission::where('employee_id', '=', $id)->get();
        $total_commission = 0;
        foreach ($commissions as $commission) {
            $total_commission = $commission->amount + $total_commission;
        }
        $commission_json = json_encode($commissions);

        return $commission_json;

    }

    public static function loan($id)
    {
        //Loan
        $loans = Loan::where('employee_id', '=', $id)->get();
        $total_loan = 0;
        foreach ($loans as $loan) {
            $total_loan = $loan->amount + $total_loan;
        }
        $loan_json = json_encode($loans);

        return $loan_json;
    }

    public static function saturation_deduction($id)
    {
        //Saturation Deduction
        $saturation_deductions = SaturationDeduction::where('employee_id', '=', $id)->get();
        $total_saturation_deduction = 0;
        foreach ($saturation_deductions as $saturation_deduction) {
            $total_saturation_deduction = $saturation_deduction->amount + $total_saturation_deduction;
        }
        $saturation_deduction_json = json_encode($saturation_deductions);

        return $saturation_deduction_json;

    }

    public static function other_payment($id)
    {
        //OtherPayment
        $other_payments = OtherPayment::where('employee_id', '=', $id)->get();
        $total_other_payment = 0;
        foreach ($other_payments as $other_payment) {
            $total_other_payment = $other_payment->amount + $total_other_payment;
        }
        $other_payment_json = json_encode($other_payments);

        return $other_payment_json;
    }

    public static function overtime($id)
    {
        //Overtime
        $over_times = Overtime::where('employee_id', '=', $id)->get();
        $total_over_time = 0;
        foreach ($over_times as $over_time) {
            $total_work = $over_time->number_of_days * $over_time->hours;
            $amount = $total_work * $over_time->rate;
            $total_over_time = $amount + $total_over_time;
        }
        $over_time_json = json_encode($over_times);

        return $over_time_json;
    }

    public static function employee_id()
    {
        $employee = Employee::latest()->first();

        return !empty($employee) ? $employee->id + 1 : 1;
    }

    public function branch()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'owned_by');
    }
    public function userbranch()
    {
        return $this->hasOne('App\Models\User', 'id', 'owned_by');
    }
    public function branchdetail()
    {
        return $this->belongsTo('App\Models\User', 'id', 'owned_by');
    }
    public function ownedBranch()
    {
        return $this->belongsTo('App\Models\User', 'owned_by', 'id');
    }

    public function department()
    {
        return $this->hasOne('App\Models\Department', 'id', 'department_id');
    }
    
    public function latestEducation()
    {
        return $this->hasOne('App\Models\EmpEducation', 'emp_id', 'id')->orderByDesc('pass_date');
    }
    public function resignation()
    {
        return $this->hasOne('App\Models\Resignation', 'employee_id', 'id');
    }
    public function termination()
    {
        return $this->hasOne('App\Models\Termination', 'employee_id', 'id');
    }

    public function designation()
    {
        return $this->hasOne('App\Models\Designation', 'id', 'designation_id');
    }

    public function salaryType()
    {
        return $this->hasOne('App\Models\PayslipType', 'id', 'salary_type');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'branch_id');
    }

    public function paySlip()
    {
        return $this->hasOne('App\Models\PaySlip', 'id', 'employee_id');
    }


    public function present_status($employee_id, $data)
    {
        return AttendanceEmployee::where('employee_id', $employee_id)->where('date', $data)->first();
    }

    public function eobi($employee_id, $branches_id)
    {
        $branches_school = \App\Models\SchoolDetails::where('branch_id', $branches_id)->first();
        $employee = Employee::where('id', $employee_id)->first();
        $eobiValue = ($employee->eobi / 100) * ($branches_school->eobi_values ?? 0);
        $eobiEmployerValue = ($employee->eobi_employer / 100) * ($branches_school->eobi_values ?? 0);
        return [
            'employee_eobi' => $eobiValue,
            'employer_eobi' => $eobiEmployerValue,
        ];
    }


    public static function employee_salary($salary)
    {
        $employee = Employee::where("salary", $salary)->first();
        if ($employee->salary == '0' || $employee->salary == '0.0') {
            return "-";
        } else {
            return $employee->salary;
        }
    }
    public function getEmployeeTenure($employeeId)
    {
        $employee = Employee::with('employee_rejoin')->find($employeeId);

        if (!$employee) {
            return 'N/A';
        }

        $totalYears = 0;
        $totalMonths = 0;

        // Add tenure from rejoin history
        if ($employee->employee_rejoin && $employee->employee_rejoin->count() > 0) {
            foreach ($employee->employee_rejoin as $rejoin) {
                $start = Carbon::parse($rejoin->prev_doj);
                $end = Carbon::parse($rejoin->prev_leaving_date);
                $diff = $start->diff($end);

                $totalYears += $diff->y;
                $totalMonths += $diff->m;
            }
        }

        // Add current tenure
        $currentDoj = Carbon::parse($employee->company_doj);
        $currentDiff = $currentDoj->diff(now());

        $totalYears += $currentDiff->y;
        $totalMonths += $currentDiff->m;

        // Convert overflow months to years
        $totalYears += intdiv($totalMonths, 12);
        $totalMonths = $totalMonths % 12;

        return "{$totalYears}years {$totalMonths}Months";
    }



}

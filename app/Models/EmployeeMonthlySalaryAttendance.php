<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMonthlySalaryAttendance extends Model
{
    use HasFactory;
    protected $fillable=[
        'employee_id',
        'working_days',
        'absents',
        'total_annual',
        'bal_annual',
        'total_casual',
        'bal_casual',
        'leave',
        'month_days',
        'for_month_of',
        'gm_final',
        'sal_final',
        'adm_final',
        'accountant_finalize',
        'lock_status',
        'owned_by',
        'created_by',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
    public function employeemonthlysalary(){
        return $this->belongsTo(EmployeeMonthlySalary::class, 'employee_id', 'employee_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMonthlySalary extends Model
{
    use HasFactory;
    protected $fillable=[
        'employee_id',
        'department_id',
        'paymode',
        'bank_from_id',
        'account_number',
        'scale_id',
        'scale_no',
        'salary_date',
        'paid_date',
        'sal_days',
        'basics',
        'conv',
        'chaild_con',
        'misc',
        'drns',
        'other_add',
        'stop_sal',
        'other',
        'gross',
        'loan',
        'emp_sec',
        'pessi',
        'pessi_employer',
        'it',
        'eobi',
        'eobi_employer',
        'dedu',
        'tra_course',
        'sal_advance',
        'prc_final',
        'sal_final',
        'on_hold',
        'net_pay',
        'voucher_id',
        'status',
        'owned_by',
        'created_by',
    ];
    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id','id');
    }
    public function employee_scale(){
        return $this->belongsTo(Employee::class,'employee_id','employee_id');
    }
    public function salaryheads(){
        return $this->hasMany(EmployeeMonthlySalaryHeads::class, 'scale_no', 'scale_no');
    }
    public function salary_heads(){
        return $this->hasMany(EmployeeMonthlySalaryHeads::class, 'sal_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryProposal extends Model
{
    use HasFactory;
    protected $fillable = [
        'emp_no',
        'payscale',
        'income_tax',
        'other_deduction',
        'EOBI',
        'gross',
        'net_salary',
        'pay_method',
        'bank_name',
        'bank_account',
        'status',
        'created_by',
        'owned_by',
    ];
    public function employees()
    {
        return $this->belongsTo(Employee::class, 'emp_no', 'user_id');
    }
    public function employee_payscale()
    {
        return $this->belongsTo(EmployeeScale::class,'payscale','id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePayscaleDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id','appletter', 'paymode', 'account_number', 'account_id', 'department_id', 'pay_scale_id',
        'effect_from','working_days', 'drns', 'conv', 'misc', 'chaild_concession', 'emp_sec', 'security_receive_account',
        'itax', 'tax_payable_account', 'eobi', 'eobi_employer', 'eobi_payable_account', 'pessi', 'pessi_employer',
        'pessi_payable_account', 'other_deduction', 'other_dedu_payable_account', 'advance','other_add',
        'advance_payable_account', 'net', 'net_payable_account','owned_by','created_by',
    ];

    public function scale(){
        return $this->belongsTo(EmployeeScale::class,'pay_scale_id','id');
    }
    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id','id');
    }
}

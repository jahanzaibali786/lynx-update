<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAdvance extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id',
        'advance_amount',
        'advance_date',
        'advance_reason',
        'status',
        'approval_date',
        'bank_id',
        'chartaccount_id',
        'reference',
        'payment_method',
        'voucher_id',
        'approved_by',
        'deducted_salary_id',
        'created_by',
        'owned_by',
    ];


    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }

    public function approvedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'approved_by');
    }
}

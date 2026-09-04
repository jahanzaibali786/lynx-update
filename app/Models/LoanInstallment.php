<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanInstallment extends Model
{
    protected $fillable = [
        'loan_id',
        'installment_no',
        'due_month',
        'amount',
        'paid_amount',
        'status',
        'salary_id',
        'salary_deduction_detail_id',
        'paid_at',
        'owned_by',
        'created_by',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function salary()
    {
        return $this->belongsTo(EmployeeMonthlySalary::class, 'salary_id');
    }

    public function deductionDetail()
    {
        return $this->belongsTo(SalaryDeductionDetail::class, 'salary_deduction_detail_id');
    }

    public function getDueAmountAttribute()
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryDeductionDetail extends Model
{
    protected $table = 'salary_deduction_details';

    protected $fillable = [
        'salary_id',
        'employee_id',
        'type',
        'sub_type',
        'reference_id',
        'amount',
        'note',
        'coa_id',
    ];

    /**
     * Relationship: Salary
     */
    public function salary()
    {
        return $this->belongsTo(EmployeeMonthlySalary::class, 'salary_id');
    }

    /**
     * Relationship: COA (Chart of Accounts)
     */
    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    /**
     * Scope: Loans
     */
    public function scopeLoans($query)
    {
        return $query->where('type', 'loan');
    }

    /**
     * Scope: Advances
     */
    public function scopeAdvances($query)
    {
        return $query->where('type', 'advance');
    }

    /**
     * Scope: Security
     */
    public function scopeSecurity($query)
    {
        return $query->where('type', 'security');
    }
}
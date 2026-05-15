<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'branches',
        'employee_id',
        'title',
        'department',
        'emp_sec',
        'maxamount',
        'approval_date',
        'service_tenure',
        'amount',
        'apply_date',
        'from_pay_month',
        'pay_period',
        'loan_ended',
        'reason',
        'per_month_amount',
        'received_amount',
        'bank_id',
        'chartaccount_id',
        'referance_id',
        'voucher_id',
        'approved_by',
        'status',
        'owned_by',
        'created_by',
    ];

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }

    public function approvedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'approved_by');
    }

    public function stopHistories()
    {
        return $this->hasMany(LoanStopHistory::class)->orderBy('stop_from_month', 'desc');
    }

    public function isStoppedForMonth($monthDate)
    {
        $month = \Carbon\Carbon::parse($monthDate)->startOfMonth();

        return $this->stopHistories()
            ->whereDate('stop_from_month', '<=', $month->format('Y-m-d'))
            ->whereDate('stop_to_month', '>=', $month->format('Y-m-d'))
            ->exists();
    }

    public function loan_option()
    {
        return $this->hasOne('App\Models\LoanOption', 'id', 'loan_option')->first();
    }
    public static $Loantypes=[
        'fixed'=>'Fixed',
        'percentage'=> 'Percentage',
    ];
}

<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function installments()
    {
        return $this->hasMany(LoanInstallment::class)->orderBy('installment_no');
    }

    public function pendingInstallments()
    {
        return $this->hasMany(LoanInstallment::class)->where('status', 0)->orderBy('installment_no');
    }

    public static function roundedInstallmentAmounts($amount, $installments)
    {
        $installments = max(1, (int) $installments);
        $amount = (float) $amount;
        $baseAmount = round($amount / $installments);
        $amounts = [];
        $runningTotal = 0;

        for ($i = 1; $i <= $installments; $i++) {
            $installmentAmount = $i === $installments
                ? round($amount - $runningTotal, 2)
                : $baseAmount;

            $amounts[] = $installmentAmount;
            $runningTotal += $installmentAmount;
        }

        return $amounts;
    }

    public function syncInstallmentPlan($startMonth = null)
    {
        $closedInstallments = $this->installments()
            ->where('status', 1)
            ->orderBy('installment_no')
            ->get();
        $partialInstallments = $this->installments()
            ->where('status', 0)
            ->where('paid_amount', '>', 0)
            ->orderBy('installment_no')
            ->get();
        $preservedInstallments = $closedInstallments->concat($partialInstallments);

        $preservedCount = $preservedInstallments->count();
        $paidAmount = (float) $preservedInstallments->sum(function ($installment) {
            return (float) ($installment->paid_amount ?: $installment->amount);
        });
        $allocatedAmount = (float) $preservedInstallments->sum('amount');
        $remainingCount = max(0, (int) $this->pay_period - $preservedCount);
        $remainingAmount = max(0, (float) $this->amount - $allocatedAmount);

        $this->installments()
            ->where('status', 0)
            ->where('paid_amount', '<=', 0)
            ->delete();

        if ($remainingCount <= 0 || $remainingAmount <= 0) {
            $this->received_amount = $paidAmount;
            $this->per_month_amount = 0;
            if ($preservedInstallments->isNotEmpty()) {
                $this->loan_ended = Carbon::parse($preservedInstallments->max('due_month'))->startOfMonth()->format('Y-m-d');
            }
            $this->saveQuietly();
            return;
        }

        if ($startMonth) {
            $cursor = Carbon::parse($startMonth)->startOfMonth();
        } elseif ($preservedInstallments->isNotEmpty()) {
            $cursor = Carbon::parse($preservedInstallments->max('due_month'))->startOfMonth()->addMonth();
        } else {
            $cursor = Carbon::parse($this->from_pay_month)->startOfMonth();
        }

        if ($preservedInstallments->isNotEmpty()) {
            $minimumMonth = Carbon::parse($preservedInstallments->max('due_month'))->startOfMonth()->addMonth();
            if ($cursor->lt($minimumMonth)) {
                $cursor = $minimumMonth;
            }
        }

        $amounts = self::roundedInstallmentAmounts($remainingAmount, $remainingCount);
        $installmentNo = $preservedInstallments->isNotEmpty() ? ((int) $preservedInstallments->max('installment_no')) + 1 : 1;
        $lastDueMonth = null;

        foreach ($amounts as $amount) {
            while ($this->isStoppedForMonth($cursor)) {
                $cursor->addMonth();
            }

            $lastDueMonth = $cursor->copy();

            LoanInstallment::create([
                'loan_id' => $this->id,
                'installment_no' => $installmentNo,
                'due_month' => $cursor->format('Y-m-d'),
                'amount' => $amount,
                'paid_amount' => 0,
                'status' => 0,
                'owned_by' => $this->owned_by,
                'created_by' => $this->created_by,
            ]);

            $installmentNo++;
            $cursor->addMonth();
        }

        $this->received_amount = $paidAmount;
        $this->per_month_amount = $amounts[0] ?? 0;
        if ($lastDueMonth) {
            $this->loan_ended = $lastDueMonth->format('Y-m-d');
        }
        $this->saveQuietly();
    }

    public function nextPayableInstallment($monthDate)
    {
        $month = Carbon::parse($monthDate)->startOfMonth();

        return $this->installments()
            ->where('status', 0)
            ->whereDate('due_month', '<=', $month->format('Y-m-d'))
            ->orderBy('installment_no')
            ->first();
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

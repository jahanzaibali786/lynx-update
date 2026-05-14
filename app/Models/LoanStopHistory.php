<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanStopHistory extends Model
{
    protected $fillable = [
        'loan_id',
        'stop_from_month',
        'stop_to_month',
        'months',
        'reason',
        'owned_by',
        'created_by',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}

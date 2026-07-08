<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AdvanceTaxCollection extends Model
{
    protected $fillable = [
        'employee_id',
        'tax_month',
        'collection_date',
        'amount',
        'payment_method',
        'reference',
        'remarks',
        'proof_picture',
        'status',
        'approval_date',
        'approved_by',
        'owned_by',
        'created_by',
    ];

    public static $statuses = [
        0 => 'Pending',
        1 => 'Approved',
        2 => 'Rejected',
    ];

    public static $paymentMethods = [
        'online' => 'OL',
        'cheque' => 'CHK',
        'cash' => 'CSH',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class, 'id', 'employee_id');
    }

    public function approvedBy()
    {
        return $this->hasOne(User::class, 'id', 'approved_by');
    }
}

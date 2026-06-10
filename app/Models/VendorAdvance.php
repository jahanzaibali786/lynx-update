<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vender_id',
        'advance_amount',
        'advance_date',
        'advance_reason',
        'status',
        'approval_date',
        'bank_id',
        'reference',
        'payment_method',
        'voucher_id',
        'approved_by',
        'owned_by',
        'created_by',
    ];

    public function vendor()
    {
        return $this->hasOne(Vender::class, 'id', 'vender_id');
    }

    public function bank()
    {
        return $this->hasOne(BankAccount::class, 'id', 'bank_id');
    }

    public function approvedBy()
    {
        return $this->hasOne(User::class, 'id', 'approved_by');
    }
}

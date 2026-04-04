<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransfer extends Model
{
    protected $fillable = [
        'from_account',
        'to_account',
        'amount',
        'previous_balance',
        'date',
        'payment_method',
        'reference',
        'description',
        'voucher_id',
        'owned_by',
        'created_by',
    ];

    public function fromBankAccount()
    {
        return $this->hasOne('App\Models\BankAccount', 'id', 'from_account')->first();
    }

    public function toBankAccount()
    {
        return $this->hasOne('App\Models\BankAccount', 'id', 'to_account')->first();
    }
    //journal
    public function journals()
    {
        return $this->hasOne('App\Models\JournalEntry', 'id', 'voucher_id')->first();
    }
    //items
    public function items()
    {
        return $this->hasMany('App\Models\JournalItem', 'journal', 'voucher_id')->get();
    }
}

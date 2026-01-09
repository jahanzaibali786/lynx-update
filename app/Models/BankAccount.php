<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'holder_name',
        'bank_name',
        'account_number',
        'chart_account_id',
        'opening_balance',
        'contact_number',
        'bank_address',
        'owned_by',
        'created_by',
    ];

    public function chartAccount()
    {
        return $this->hasOne('App\Models\ChartOfAccount', 'id', 'chart_account_id');
    }
    public function branch()
    {
        return $this->hasOne('App\Models\User', 'id', 'owned_by');
    }


}


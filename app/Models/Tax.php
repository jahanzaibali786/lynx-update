<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = [
        'name', 'rate','account_income','account_expance','owned_by', 'created_by'
    ];

    public function account_incomes()
    {
        return $this->belongsTo('App\Models\ChartOfAccount', 'account_income', 'id');
    }
    public function account_expances()
    {
        return $this->belongsTo('App\Models\ChartOfAccount', 'account_expance', 'id');
    }
}

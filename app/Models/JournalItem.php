<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalItem extends Model
{
    protected $fillable = [
        'journal',
        'account',
        'head',
        'user_id',
        'bank_id',
        'user_type',
        'entry_id',
        'types',
        'is_discount',
        'description',
        'head_ids',
        'branch_id',
        'debit',
        'credit',
        'created_at',
        'updated_at',
    ];

    public function accounts()
    {
        return $this->hasOne('App\Models\ChartOfAccount', 'id', 'account');
    }

    public function heads(){
        return $this->hasOne('App\Models\FeeHead','id','head');
    }
    public function receiptheads(){
        return $this->hasOne('App\Models\ChallanHead','id','head');
    }
    public function user()
    {
        return $this->hasOne('App\Models\StudentRegistration', 'id', 'user_id');
    }
    
    public function journalEntery(){
        return $this->hasOne('App\Models\JournalEntry', 'id', 'journal');
    }
    //bank
    public function bank()
    {
        return $this->hasOne('App\Models\BankAccount', 'id', 'bank_id');
    }
}

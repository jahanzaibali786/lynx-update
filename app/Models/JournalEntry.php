<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'date',
        'challan_id',
        'reference',
        'description',
        'journal_id',
        'bank_id',
        'user_id',
        'user_type',
        'voucher_type',
        'reference_id',
        'category',
        'owned_by',
        'created_by',
        'added_by',
        'added_at',
        'updated_by',
        'approved_by',
        'approved_at',
        'is_system_generated',
        'attachment',
        'status',
        'payment_mode',
        'cheque_no',
        'cheque_date',
        'transaction_no',
        'reversed_entry_id',
        'reversed_timestamp',
        'created_at',
        'updated_at',
    ];


    public function accounts()
    {
        return $this->hasmany('App\Models\JournalItem', 'journal', 'id');
    }
    public function items()
    {
        return $this->hasMany('App\Models\JournalItem', 'journal', 'id');
    }
    public function totalCredit()
    {
        $total = 0;
        foreach($this->accounts as $account)
        {
            $total += $account->credit;
        }

        return $total;
    }

    public function totalDebit()
    {
        $total = 0;
        foreach($this->accounts as $account)
        {
            $total += $account->debit;
        }

        return $total;
    }

    public function bank()
    {
        return $this->hasOne('App\Models\BankAccount', 'id', 'bank_id');
    }
    public function user()
    {
        return $this->hasOne('App\Models\StudentRegistration', 'id', 'user_id');
    }

    public function studypackchallan()
    {
        return $this->hasOne('App\Models\StudyPackChallans', 'id', 'reference_id')->where('challan_type', 'Studypack');
    }
    public function challan()
    {
        return $this->hasOne('App\Models\Challans', 'id', 'reference_id')->whereIn('challan_type', ['Admission','Advance', 'Registration','Readmission','Regular','Transfer','Withdrawal']);
    }
    public function stdRecp()
    {
        return $this->hasOne('App\Models\StudypackReceipts', 'id', 'reference_id');
    }
    public function recipt()
    {
        return $this->hasOne('App\Models\StudentReceipt', 'id', 'reference_id');
    }
    public function branch()
    {
        return $this->hasOne('App\Models\User', 'id', 'owned_by');
    }
    public function categoryType()
    {
        return $this->belongsTo('App\Models\ProductServiceCategory', 'category_type_id');
    }
}

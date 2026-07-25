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
        'voucher_series',
        'manual_series_no',
        'manual_reference',
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
        'payee_account_title',
        'payee_account_no',
        'payee_contact',
        'payee_email',
        'payee_cnic',
        'receiver_name',
        'receiver_cnic',
        'receiver_contact',
        'receiver_email',
        'payment_date',
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

    public function getVoucherNumber()
    {
        if ($this->voucher_series === 'MANUAL') {
            return $this->manual_reference;
        }

        $voucherType = strtoupper($this->voucher_type ?? 'JV');
        $methodMap = [
            'BRV' => 'BRVNumberFormat',
            'BPV' => 'BPVNumberFormat',
            'CRV' => 'CRVNumberFormat',
            'CPV' => 'CPVNumberFormat',
        ];
        $method = $methodMap[$voucherType] ?? 'journalNumberFormat';

        if (\Auth::check()) {
            return \Auth::user()->$method($this->journal_id);
        }

        return $voucherType . sprintf("%05d", $this->journal_id);
    }
    public function securityAdjustment()
    {
        return $this->hasOne('App\Models\ChallanSecAdjustment', 'voucher_id', 'id');
    }
}

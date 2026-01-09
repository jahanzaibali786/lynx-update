<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentReceipt extends Model
{
    use HasFactory,LogsActions;

    protected $fillable = [
        'recipt_date',
        'challan_id',
        'student_id',
        'recipt_amount',
        'challan_amount',
        'late_amount',
        'arrears',
        'bank_id',
        "account_id",
        'voucher_id',
        'referance',
        'receive_type',
        'received_by',
        'owned_by',
        'created_by',
    ];

    public function challan()
    {
        return $this->belongsTo(Challans::class, 'challan_id', 'id');
    }
    public function received()
    {
        return $this->belongsTo(User::class, 'received_by', 'id');
    }
    public function bank()
    {
        return $this->belongsTo(BankAccount::class, 'bank_id', 'id');
    }
    public function voucher()
    {
        return $this->hasMany(JournalItem::class, 'journal', 'voucher_id');
    }
    // public function voucher()
    // {
    //     return $this->hasMany(JournalItem::class, 'journal', 'voucher_id')->where('credit','!=','0');
    // }
}

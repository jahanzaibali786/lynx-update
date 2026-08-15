<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id',
        'journal_id',
        'salary_id',
        'amount',
        'net_pay',
        'bank_id',
        'account_number',
        'payment_method',
        'payment_date',
        'reference',
        'description',
        'owned_by',
        'created_by',
    ];

    public function journal()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_id');
    }
}

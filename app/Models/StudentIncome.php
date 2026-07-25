<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentIncome extends Model
{
    use HasFactory;

    protected $table = 'student_incomes';

    protected $fillable = [
        'date',
        'student_id',
        'income_type',
        'amount',
        'bank_id',
        'coa_id',
        'description',
        'received_by',
        'created_by',
        'owned_by',
        'journal_entry_id',
    ];

    public function student()
    {
        return $this->belongsTo(StudentRegistration::class, 'student_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(User::class, 'owned_by', 'id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by', 'id');
    }

    public function bank()
    {
        return $this->belongsTo(BankAccount::class, 'bank_id', 'id');
    }

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id', 'id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id', 'id');
    }
}

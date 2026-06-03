<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challans extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'class_id',
        'rollno',
        'challanNo',
        'concession_id',
        'challan_date',
        'fee_month',
        'other_months',
        'challan_type',
        'issue_date',
        'due_date',
        'remarks',
        'paid_amount',
        'total_amount',
        'concession_amount',
        'status',
        'temp_status',
        'session_id',
        'owned_by',
        'created_by',
        'voucher_id',
        'created_at',
        'updated_at',
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'id');
    }
    // public function section()
    // {
    //     return $this->belongsTo(Section::class, 'section_id', 'id');
    // }
    public function branch()
    {
        return $this->belongsTo(User::class, 'owned_by', 'id');
    }
    public function heads()
    {
        return $this->hasMany(ChallanHead::class, 'challan_id', 'id');
    }
    public function unpaidHeads()
    {
        return $this->hasMany(ChallanHead::class, 'challan_id', 'id')
            ->whereRaw('price != (concession + paid)');
    }

    public function student()
    {
        return $this->belongsTo(StudentRegistration::class, 'student_id', 'id');
    }
    public function getStudentAttribute()
    {
        // if ($this->studentCache != null) {
        //     return $this->studentCache;
        // }
        // if ($this->relationLoaded('student') && $this->getRelation('student')) {
        //     return $this->studentCache = $this->getRelation('student');
        // }
        $student = StudentRegistration::find($this->student_id);
        if (!$student) {
            $student = StudentRegistration::where('reg_no', $this->student_id)->first();
        }
        return $student;
    }
    public function enrollstudent()
    {
        return $this->belongsTo(StudentEnrollments::class, 'student_id', 'regId');
    }
    public function concession()
    {
        return $this->belongsTo(Concession::class, 'concession_id', 'id');
    }
    public function receipts()
    {
        return $this->hasMany(StudentReceipt::class, 'challan_id');
    }
    public function vouchers()
    {
        return $this->hasMany(JournalEntry::class, 'reference_id');
    }

    public function master()
    {
        return $this->belongsTo(SchoolDetails::class, 'owned_by', 'branch_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'voucher_id');
    }

    public function journalItems()
    {
        return $this->hasMany(JournalItem::class, 'journal', 'voucher_id');
    }
}

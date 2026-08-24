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
        'section_id',
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
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
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
        // Prefer the eager-loaded relation when present. Accessors take
        // precedence over relations for property access, so without this the
        // `with('student')` eager load is ignored and every `$challan->student`
        // access triggers a fresh query (N+1). Falls through to the reg_no
        // lookup only for legacy rows where student_id references reg_no.
        if ($this->relationLoaded('student')) {
            $loaded = $this->getRelation('student');
            if ($loaded) {
                return $loaded;
            }
        }
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

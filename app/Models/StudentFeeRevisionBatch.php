<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFeeRevisionBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'revision_type',
        'promotion_id',
        'student_id',
        'reg_id',
        'session_from_id',
        'session_to_id',
        'branch_from_id',
        'branch_to_id',
        'class_from_id',
        'class_to_id',
        'section_from_id',
        'section_to_id',
        'effective_from',
        'status',
        'remarks',
        'owned_by',
        'created_by',
    ];

    public function items()
    {
        return $this->hasMany(StudentFeeRevisionItem::class, 'batch_id');
    }

    public function student()
    {
        return $this->belongsTo(StudentEnrollments::class, 'student_id', 'enrollId');
    }

    public function registration()
    {
        return $this->belongsTo(StudentRegistration::class, 'reg_id');
    }

    public function sessionFrom()
    {
        return $this->belongsTo(Session::class, 'session_from_id');
    }

    public function sessionTo()
    {
        return $this->belongsTo(Session::class, 'session_to_id');
    }

    public function branchFrom()
    {
        return $this->belongsTo(User::class, 'branch_from_id');
    }

    public function branchTo()
    {
        return $this->belongsTo(User::class, 'branch_to_id');
    }

    public function classFrom()
    {
        return $this->belongsTo(Classes::class, 'class_from_id');
    }

    public function classTo()
    {
        return $this->belongsTo(Classes::class, 'class_to_id');
    }

    public function sectionFrom()
    {
        return $this->belongsTo(Section::class, 'section_from_id');
    }

    public function sectionTo()
    {
        return $this->belongsTo(Section::class, 'section_to_id');
    }
}

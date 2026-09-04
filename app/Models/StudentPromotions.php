<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPromotions extends Model
{
    use HasFactory , LogsActions;
    protected $fillable = [
        'student_id',
        'prev_session',
        'new_session',
        'class_from',
        'class_to',
        'prev_section',
        'new_section',
        'branch_from',
        'branch_to',
        'promotion_date',
        'owned_by',
        'created_by',
    ];

    public function student()
    {
        return $this->belongsTo(StudentEnrollments::class, 'student_id', 'enrollId');
    }

    public function registration()
    {
        return $this->hasOneThrough(
            StudentRegistration::class,
            StudentEnrollments::class,
            'enrollId',
            'id',
            'student_id',
            'regId'
        );
    }

    public function prevSession()
    {
        return $this->belongsTo(Session::class, 'prev_session');
    }

    public function newSession()
    {
        return $this->belongsTo(Session::class, 'new_session');
    }

    public function classFrom()
    {
        return $this->belongsTo(Classes::class, 'class_from');
    }

    public function classTo()
    {
        return $this->belongsTo(Classes::class, 'class_to');
    }

    public function sectionFrom()
    {
        return $this->belongsTo(Section::class, 'prev_section');
    }

    public function sectionTo()
    {
        return $this->belongsTo(Section::class, 'new_section');
    }

    public function branchFrom()
    {
        return $this->belongsTo(User::class, 'branch_from');
    }

    public function branchTo()
    {
        return $this->belongsTo(User::class, 'branch_to');
    }
}

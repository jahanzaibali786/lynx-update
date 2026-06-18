<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentWithdrawal extends Model
{
    use HasFactory, LogsActions;
    protected $fillable = [
        'student_id',
        'challan_id',
        'session_id',
        'class_id',
        'branch_id',
        'withdraw_date',
        'reason',
        'remark',
        'other_reason',
        'approved_by',
        'status',
        'created_by',
        'owned_by',
    ];

    protected $studentCache = null;

    public function student()
    {
        return $this->belongsTo(StudentRegistration::class, 'student_id', 'id');
    }

    public function getStudentAttribute()
    {
        if ($this->studentCache != null) {
            return $this->studentCache;
        }
        if ($this->relationLoaded('student') && $this->getRelation('student')) {
            return $this->studentCache = $this->getRelation('student');
        }
        $student = StudentRegistration::find($this->student_id);
        if (!$student) {
            $student = StudentRegistration::where('reg_no', $this->student_id)->first();
        }
        return $this->studentCache = $student;
    }


    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }
    public function challan()
    {
        return $this->belongsTo(Challans::class, 'challan_id', 'challanNo');
    }

    public function branch()
    {
        return $this->hasOne('App\Models\User', 'id', 'branch_id');
    }

    public function class()
    {
        return $this->hasOne('App\Models\Classes', 'id', 'class_id');
    }
    protected $casts = [
    'withdraw_date' => 'date',
];

}

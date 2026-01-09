<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpChildrens extends Model
{
    use HasFactory;

    protected $fillable = [
        'emp_id',
        'student_id',
        'branch_id',
        'class_id',
        'amount',
    ];

    public function student()
    {
        return $this->belongsTo(StudentRegistration::class, 'student_id', 'roll_no');
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
    //enrollment 
    public function enrollment()
    {
        return $this->hasOne(StudentEnrollments::class, 'regId', 'student_id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id', 'id');
    }
}

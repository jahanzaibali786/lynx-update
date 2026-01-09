<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTransfer extends Model
{
    use HasFactory,LogsActions;

    public function student()
    {
        return $this->belongsTo(StudentRegistration::class, 'student_id', 'roll_no');
    }
    public function enrollment()
    {
        return $this->belongsTo(StudentEnrollments::class, 'student_id', 'enrollId');
    }
    public function challan()
    {
        return $this->belongsTo(Challans::class, 'challan_id', 'id');
    }
    public function classfrom()
    {
        return $this->belongsTo(Classes::class, 'class_from', 'id');
    }
    public function classto()
    {
        return $this->belongsTo(Classes::class, 'class_to', 'id');
    }
    public function sectionfrom()
    {
        return $this->belongsTo(Section::class, 'section_from', 'id');
    }
    public function sectionto()
    {
        return $this->belongsTo(Section::class, 'section_to', 'id');
    }

    public function branchfrom()
    {
        return $this->hasOne('App\Models\User', 'id', 'branch_from');
    }

    public function branchto()
    {
        return $this->hasOne('App\Models\User', 'id', 'branch_to');
    }

}

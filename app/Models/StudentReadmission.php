<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentReadmission extends Model
{
    use HasFactory,LogsActions;

    public function student()
    {
        return $this->belongsTo(StudentRegistration::class, 'student_id', 'roll_no');
    }
    public function challan()
    {
        return $this->belongsTo(Challans::class, 'challan_id', 'id');
    }
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'id');
    }
    public function branch()
    {
        return $this->hasOne('App\Models\User', 'id', 'branch_id');
    }
    public function session()
    {
        return $this->hasOne('App\Models\Session', 'id', 'session_id');
    }
}

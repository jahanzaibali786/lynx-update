<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentEnrollments extends Model
{
    use HasFactory , LogsActions;
    protected $fillable = ['regId', 'enrollId','adm_date','adm_branch','adm_session', 'class_id','section_id','session_id','active_status', 'owned_by', 'created_by'];


    public function StudentRegistration()
    {
        return $this->belongsTo(StudentRegistration::class, 'regId', 'id');
    }
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }
    public function withdrawal()
    {
        return $this->belongsTo(StudentWithdrawal::class, 'regId', 'student_id');
    }
    public function branch()
    {
        return $this->belongsTo(User::class, 'owned_by', 'id');
    }
    
    public function admbranch()
    {
        return $this->belongsTo(User::class, 'adm_branch', 'id');
    }

    public function master()
    {
        return $this->belongsTo(SchoolDetails::class, 'owned_by', 'branch_id');
    }
}

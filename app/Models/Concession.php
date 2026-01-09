<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concession extends Model
{
    use HasFactory;
    
    public function student()
    {
        return $this->belongsTo(StudentRegistration::class, 'student_id', 'id');
    }
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'id');
    }
    public function concession()
    {
        return $this->belongsTo(ConcessionPolicy::class, 'concession_id', 'id');
    }
    public function policy_head()
    {
        return $this->hasMany('App\Models\ConcessionPolicyHead', 'concession_id', 'id');
    }
    public function branches()
    {
        return $this->belongsTo(User::class, 'owned_by', 'id');
    }
    public function branches_address()
    {
        return $this->belongsTo(SchoolDetails::class, 'owned_by', 'id');
    }

}

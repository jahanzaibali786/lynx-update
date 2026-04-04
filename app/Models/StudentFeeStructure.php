<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'reg_id',
        'student_id',
        'head_id',
        'class_id',
        'branch_id',
        'checked_status',
        'amount',
        'discount',
        'is_custom',
        'owned_by',
        'created_by',
    ];  
    public function feehead()
    {
        return $this->belongsTo('App\Models\FeeHead', 'head_id', 'id');
    }
    public function student()
    {
        return $this->belongsTo('App\Models\StudentRegistration', 'reg_id', 'id');
    }
}


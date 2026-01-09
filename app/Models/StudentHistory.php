<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'reg_id',
        'student_id',
        'event_type',
        'from_session_id',
        'from_class_id',
        'from_section_id',
        'from_branch_id',
        'to_session_id',
        'to_class_id',
        'to_section_id',
        'to_branch_id',
        'effective_date',
        'remarks',
        'owned_by',
        'created_by',
    ];
}

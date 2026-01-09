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
}

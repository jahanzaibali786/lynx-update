<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionLogs extends Model
{
 use HasFactory;
     protected $fillable = [
        'subject_type',
        'subject_id',
        'user_id',
        'ip_address',
        'action',
        'changes',
    ];
    protected $casts = ['changes'=>'array'];
}

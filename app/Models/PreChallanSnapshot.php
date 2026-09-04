<?php
// app/Models/PreChallanSnapshot.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreChallanSnapshot extends Model
{
    protected $fillable = [
        'report_reference_id',
        'student_id',
        'month',
        'gross',
        'arrears',
        'net_receivable',
        'prev_month_amount',
        'difference',
        'owned_by',
        'created_by',
    ];

    public function report()
    {
        return $this->belongsTo(PreChallanReport::class, 'report_reference_id');
    }

    public function student()
    {
        return $this->belongsTo(StudentRegistration::class, 'student_id');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\User::class, 'owned_by');
    }
}
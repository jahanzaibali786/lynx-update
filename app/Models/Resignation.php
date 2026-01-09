<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resignation extends Model
{
    protected $fillable = [
        'employee_id',
        'branch_id',
        'notice_date',
        'resignation_date',
        'last_attendance_date',
        'description',
        'appr_rej_desc',
        'status',
        'owned_by',
        'created_by',
    ];

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }
}

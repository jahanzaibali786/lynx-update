<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthInsurance extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id',
        'branch_id',
        'plan_name',
        'plan_type',
        'plan_start',
        'plan_end',
        'plan_amount',
        'created_by',
        'owned_by',
        'status',
    ];
    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'scale_no',
        // 'initial_basic',
        // 'house_rent',
        // 'medical',
        // 'allowances',
        // 'gross_pay',
        'adhoc',
        'effect_from',
        'department_id',
        'status',
        'owned_by',
        'created_by',
    ];
    
    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id', 'id');
    }
    public function employeeScaleHeads()
    {
        return $this->hasMany(EmployeeScaleHeads::class, 'scale_id', 'id');
    }
    public function employeepayScaledetailHeads()
    {
        return $this->hasMany(EmployeePayscaleDetail::class, 'id', 'id');
    }

}

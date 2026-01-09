<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMonthlySalaryHeads extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'sal_id',
        'scale_id',
        'scale_no',
        'salary_date',
        'head_id',
        'head_value',
        'owned_by',
        'created_by',
    ];

    public function SalaryHead(){
        return $this->belongsTo(SalaryHeads::class, 'head_id', 'id');
    }
}

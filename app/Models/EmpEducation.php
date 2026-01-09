<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpEducation extends Model
{
    use HasFactory;
    protected $fillable = [
        'emp_id',
        'institute',
        'degree',
        'title',
        'subject',
        'adm_date',
        'pass_date',
        'grade',
        'reason',
    ];
    //EMPLOYEE 
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id');
    }
}

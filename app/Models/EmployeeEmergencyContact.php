<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEmergencyContact extends Model
{
    protected $fillable = [
        'employee_id',
        'contact_name',
        'relationship',
        'phone'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
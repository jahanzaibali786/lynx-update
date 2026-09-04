<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeChildAdjustment extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'employee_id',
        'challan_id',
        'account_id',
        'voucher_id',
        'adjust_amount',
        'adj_type',
        'type',
        'ref',
        'owned_by',
        'created_by',
    ];
}

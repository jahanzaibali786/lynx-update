<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeaves extends Model
{
    use HasFactory , LogsActions;
    protected $fillable = [
        'employee_id',       
        'casual_total',
        'casual_consumed',
        'annual_total',
        'annual_consumed',
        'owned_by',
        'created_by',
    ];
    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id','id');
    }
 	public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'id');
    }
}

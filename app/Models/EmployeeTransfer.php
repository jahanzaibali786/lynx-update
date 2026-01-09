<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTransfer extends Model
{
    use HasFactory , LogsActions;

    protected $fillable = [
            'employee_id',
            'branch_from_id',
            'branch_to_id',
            'department_from_id',
            'department_to_id',
            'designation_from_id',
            'designation_to_id',
            'transfer_date',
            'transfer_reason',
            'status',
            'owned_by',
        'created_by',
    ];

    public function department_from()
    {
        return $this->belongsTo('App\Models\Department', 'department_from_id', 'id');
    }
    public function department_to()
    {
        return $this->belongsTo('App\Models\Department', 'department_to_id', 'id');
    }

    public function branch_from()
    {
        return $this->belongsTo('App\Models\User', 'branch_from_id','id');
    }
    public function branch_to()
    {
        return $this->belongsTo('App\Models\User', 'branch_to_id','id');
    }

    public function employee()
    {
        return $this->belongsTo('App\Models\Employee', 'employee_id', 'id');
    }
}

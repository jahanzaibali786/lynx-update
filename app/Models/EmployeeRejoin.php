<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRejoin extends Model
{
    use HasFactory , LogsActions;

    protected $fillable = [
        'employee_id',
        'rejoin_date',
        'old_department_id',
        'new_department_id',
        'old_designation_id',
        'new_designation_id',
        'prev_doj',
        'prev_leaving_date',
        'owned_by',
        'created_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeContract extends Model
{
    protected $table = 'employee_contracts';

    protected $fillable = [
        'employee_id',
        'created_by',
        'owned_by',
        'from_date',
        'to_date',
        'status',
        'remarks',
        'added_by',
        'updated_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Get the contract status.
     * Automatically show as Expired when to_date is less than today's date
     * and current database status is active.
     *
     * @param  string  $value
     * @return string
     */
    public function getStatusAttribute($value)
    {
        if ($value === 'active' && $this->to_date < date('Y-m-d')) {
            return 'expired';
        }
        return $value;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'city_zip',
        'assigned_employee_id',
        'owned_by',
        'created_by',
    ];

    public static function warehouse_id($warehouse_name)
    {


        $warehouse = DB::select(
            DB::raw("SELECT IFNULL( (SELECT id from warehouses where name = :name and created_by = :created_by limit 1), '0') as warehouse_id"), ['name' => $warehouse_name,  'created_by' => Auth::user()->creatorId(), ]
        );


        return $warehouse[0]->warehouse_id;
    }

    public function branch()
    {
        return $this->hasOne('App\Models\User', 'id', 'owned_by');
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }
}

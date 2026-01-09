<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable = [
        'department_id','name','code','job_description','owned_by','created_by'
    ];

    public function department(){
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}

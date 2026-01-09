<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use LogsActions;
    protected $fillable = [
        'name',
        'status',
        'owned_by',
        'created_by',
    ];

    public function branch(){
        return $this->hasOne('App\Models\Branch','id','branch_id');
    }
}

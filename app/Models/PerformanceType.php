<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceType extends Model
{
    protected $fillable = [
        'name',
        'owned_by',
        'created_by',
    ];


    public function types()
    {

        return $this->hasMany('App\Models\Competencies', 'type', 'id');
    }

}

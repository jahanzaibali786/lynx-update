<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeductionOption extends Model
{
    protected $fillable = [
        'name',
        'owned_by',
        'created_by',
    ];
}

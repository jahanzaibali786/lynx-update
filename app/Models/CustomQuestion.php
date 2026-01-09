<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomQuestion extends Model
{
    protected $fillable = [
        'question',
        'is_required',
        'owned_by',
        'created_by',
    ];

    public static $is_required = [
        'yes' => 'Yes',
        'no' => 'No',
    ];
}

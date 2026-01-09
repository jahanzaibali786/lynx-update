<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chair extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'price',
        'space_id',
        'type',
        'owned_by',
        'created_by',
    ];
}

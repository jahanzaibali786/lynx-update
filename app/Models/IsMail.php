<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IsMail extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'price',
        'user_id',
        'company_id',
        'owned_by',
        'created_by',
    ];
}

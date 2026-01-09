<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IsVisitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date_time',
        'user_id',
        'cnic',
        'company_id',
        'owned_by',
        'created_by',
    ];
}

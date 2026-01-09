<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpFacility extends Model
{
    use HasFactory;

    protected $fillable = [
        'emp_id',
        'title',
        'type',
        'given_date',
        'upto_date',
        'detail',
    ];
}

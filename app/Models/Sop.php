<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop extends Model
{
    use HasFactory;
    protected $fillable = ['sop_title', 'sop_type', 'sop_description','sop_date','created_by','owned_by'];
}

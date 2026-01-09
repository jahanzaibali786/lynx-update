<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConcessionPolicyHead extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'concession_id',
        'head_id',
        'percentage',
        // other fillable fields
    ];
}

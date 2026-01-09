<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxSlab extends Model
{
    use HasFactory;
    protected $fillable = [
        'no',
        'lower_limit',
        'upper_limit',
        'fixed_tax_amount',
        'prev_limit_percentage',
        'year',
    ];
}

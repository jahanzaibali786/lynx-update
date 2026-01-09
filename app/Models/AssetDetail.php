<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDetail extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'company_id',
        'asset_id',
        'quantity'
    ];
}

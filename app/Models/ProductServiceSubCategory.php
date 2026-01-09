<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductServiceSubCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'type',
        'owned_by',
        'created_by',
    ];

    public function categories()
    {
        return $this->belongsTo('App\Models\ProductServiceCategory', 'category_id', 'id');
    }
}

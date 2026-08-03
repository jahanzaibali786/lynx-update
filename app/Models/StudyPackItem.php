<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyPackItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'study_pack_id',
        'quantity',
        'tax',
        'discount',
        'price',
        'owned_by',
        'created_by',
    ];

    public function product(){
        return $this->hasOne('App\Models\ProductService', 'id', 'product_id')->first();
    }
}

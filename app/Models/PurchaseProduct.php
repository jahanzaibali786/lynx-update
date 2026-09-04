<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseProduct extends Model
{
    protected $fillable = [
        'product_id',
        'purchase_id',
        'quantity',
        'received_quantity',
        'tax',
        'discount',
        'total',
    ];

    public function product()
    {
        return $this->hasOne('App\Models\ProductService', 'id', 'product_id')->first();
    }

    public function products()
    {
        return $this->hasOne('App\Models\ProductService', 'id', 'product_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceProduct extends Model
{
    protected $fillable = [
        'product_id',
        'invoice_id',
        'quantity',
        'tax',
        'discount',
        'type',
        'total',
    ];

    public function product(){
        return $this->hasOne('App\Models\ProductService', 'id', 'product_id')->first();
    }
    public function products(){
        return $this->hasOne('App\Models\ProductService', 'id', 'product_id');
    }
    public function invoice(){
        return $this->belongsTo('App\Models\Invoice', 'id', 'invoice_id');
    }
}

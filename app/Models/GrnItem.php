<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    protected $fillable = [
        'grn_id',
        'purchase_id',
        'purchase_product_id',
        'purchase_order_no',
        'product_id',
        'condition',
        'ordered_quantity',
        'quantity',
        'price',
        'description',
    ];

    public function grn()
    {
        return $this->belongsTo(Grn::class, 'grn_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductService::class, 'product_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function purchaseProduct()
    {
        return $this->belongsTo(PurchaseProduct::class, 'purchase_product_id');
    }
}
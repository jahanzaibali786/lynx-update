<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandOrderItem extends Model
{
    use HasFactory;

    protected $table = 'branch_purchase_items';

    protected $fillable = [
        'branch_purchase_id',
        'product_id',
        'quantity',
        'shipped_quantity',
        'price',
        'tax',
        'discount',
        'description',
    ];

    public function demandOrder()
    {
        return $this->belongsTo(DemandOrder::class, 'branch_purchase_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\ProductService', 'product_id', 'id');
    }
}

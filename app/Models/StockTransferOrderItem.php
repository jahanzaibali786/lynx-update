<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferOrderItem extends Model
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
        'study_pack_id',
        'study_pack_title',
        'study_pack_class',
    ];

    public function stockTransferOrder()
    {
        return $this->belongsTo(StockTransferOrder::class, 'branch_purchase_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\ProductService', 'product_id', 'id');
    }
}

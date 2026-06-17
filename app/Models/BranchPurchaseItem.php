<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchPurchaseItem extends Model
{
    use HasFactory;

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

    public function branchPurchase()
    {
        return $this->belongsTo('App\Models\BranchPurchase', 'branch_purchase_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\ProductService', 'product_id', 'id');
    }
}

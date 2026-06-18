<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    protected $fillable = [
        'grn_id',
        'product_id',
        'condition',
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferNoteItem extends Model
{
    protected $table = 'stock_transfer_note_items';

    protected $fillable = [
        'stn_id',
        'product_id',
        'quantity',
        'type',
        'price',
        'description',
        'study_pack_id',
        'study_pack_title',
        'study_pack_class',
    ];

    /**
     * Product attached to this stock transfer item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(
            ProductService::class,
            'product_id',
            'id'
        );
    }

    /**
     * Stock transfer note attached to this item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(
            StockTransferNote::class,
            'stn_id',
            'id'
        );
    }

    /**
     * Alias stn_id as invoice_id.
     */
    public function getInvoiceIdAttribute()
    {
        return $this->attributes['stn_id'] ?? null;
    }

    public function setInvoiceIdAttribute($value)
    {
        $this->attributes['stn_id'] = $value;
    }
}

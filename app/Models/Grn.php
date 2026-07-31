<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grn extends Model
{
    protected $fillable = [
        'grn_no',
        'vendor_id',
        'warehouse_id',
        'grn_date',
        'reference_no',
        'purchase_order',
        'purchase_order_id',
        'remarks',
        'status',
        'owned_by',
        'created_by',
        'added_by',
        'approved_by',
        'voucher_id',
    ];

    public static $statues = [
        'Draft',
        'Received',
        '',
        '',
        '',
        'Fw to Ho',
        'Finalized',
        'Fw to Accounts',
        'Accounts Approved',
        'Rejected by HO',
        'Rejected by Accounts'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vender::class, 'vendor_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(warehouse::class, 'warehouse_id');
    }

    public function branch()
    {
        return $this->belongsTo(User::class, 'owned_by');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function voucher()
    {
        return $this->belongsTo(JournalEntry::class, 'voucher_id');
    }

    public function items()
    {
        return $this->hasMany(GrnItem::class, 'grn_id');
    }
    public function linkedPurchases()
    {
        return $this->belongsToMany(Purchase::class, 'grn_items', 'grn_id', 'purchase_id')
            ->whereNotNull('grn_items.purchase_id')
            ->distinct();
    }
    public function getSubTotal()
    {
        return $this->items->sum(fn($item) => (float) $item->quantity * (float) $item->price);
    }

    public function getTotalQuantity()
    {
        return $this->items->sum(fn($item) => (float) $item->quantity);
    }

    public function getTotalPrice()
    {
        return $this->items->sum(fn($item) => (float) $item->price);
    }

    public function getRoundOff()
    {
        $subtotal = (float) $this->getSubTotal();
        return round($subtotal) - $subtotal;
    }

    public function getTotal()
    {
        return round((float) $this->getSubTotal());
    }
}

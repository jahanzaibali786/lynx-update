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
        'remarks',
        'status',
        'owned_by',
        'created_by',
    ];

    public static $statues = [
        'Draft',
        'Received',
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

    public function items()
    {
        return $this->hasMany(GrnItem::class, 'grn_id');
    }

    public function getSubTotal()
    {
        return $this->items->sum(fn($item) => (float) $item->quantity * (float) $item->price);
    }
}

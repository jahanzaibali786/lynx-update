<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferOrder extends Model
{
    use HasFactory;

    protected $table = 'branch_purchases';

    protected $fillable = [
        'branch_purchase_no',
        'branch_id',
        'vender_id',
        'warehouse_id',
        'purchase_date',
        'category_id',
        'status',
        'invoice_converted',
        'created_by',
        'owned_by',
    ];

    public function branchUser()
    {
        return $this->belongsTo('App\Models\User', 'branch_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\User', 'branch_id', 'id');
    }

    public static $statues = [
        'Draft',
        'Sent',
        'Unpaid',
        'Partialy Paid',
        'Paid',
        'Fw to Ho',
        'Approved',
    ];

    public function vender()
    {
        return $this->hasOne('App\Models\Vender', 'id', 'vender_id');
    }

    public function items()
    {
        return $this->hasMany(StockTransferOrderItem::class, 'branch_purchase_id', 'id');
    }

    public function category()
    {
        return $this->hasOne('App\Models\ProductServiceCategory', 'id', 'category_id');
    }

    public function getSubTotal()
    {
        $subTotal = 0;
        foreach ($this->items as $product) {
            $subTotal += ($product->price * $product->quantity);
        }
        return $subTotal;
    }

    public function getTotal()
    {
        return ($this->getSubTotal() - $this->getTotalDiscount()) + $this->getTotalTax();
    }

    public function getTotalTax()
    {
        $totalTax = 0;
        foreach ($this->items as $product) {
            if (!empty($product->tax)) {
                $taxes = Utility::totalTaxRate($product->tax);
                $totalTax += ($taxes / 100) * ($product->price * $product->quantity - $product->discount);
            }
        }
        return $totalTax;
    }

    public function getTotalDiscount()
    {
        $totalDiscount = 0;
        foreach ($this->items as $product) {
            $totalDiscount += $product->discount;
        }
        return $totalDiscount;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\warehouse; 
use Illuminate\Support\Collection;

class StockTransferNote extends Model
{
    public const STATUS_DRAFT = 0;
    public const STATUS_SENT_FOR_APPROVAL = 1;
    public const STATUS_SENT_TO_HO = self::STATUS_SENT_FOR_APPROVAL;
    public const STATUS_APPROVED = 2;
    public const STATUS_ISSUED = 3;
    public const STATUS_PARTIAL_PAID = 4;
    public const STATUS_CLEAR_PAID = 5;
    public const STATUS_REJECTED = 6;
    public const STATUS_REJECTED_BY_HO = self::STATUS_REJECTED;

    protected $table = 'stock_transfer_notes';

    protected $fillable = [
        'sto_id',
        'stn_id',
        'store_from',
        'store_to',
        'session_id',
        'issue_date',
        'due_date',
        'approve_date',
        'ref_number',
        'status',
        'category_id',
        'shipping_via',
        'stn_type',
        'shipping_display',
        'discount_apply',
        'approved_by',
        'forwarded_by',
        'forwarded_at',
        'rejected_by',
        'rejected_at',
        'issued_by',
        'issued_at',
        'issue_to',
        'recived_by',
        'issue_by',
        'owned_by',
        'created_by',
    ];

    public static $statues = [
        'Draft',
        'Sent for Approval',
        'Approved',
        'Issued',
        'Partial Paid',
        'Clear / Paid',
        'Rejected',
    ];

    protected $casts = [
        'status' => 'integer',
        'approve_date' => 'date',
        'forwarded_at' => 'datetime',
        'rejected_at' => 'datetime',
        'issued_at' => 'datetime',
    ];

    public function tax()
    {
        return $this->hasOne('App\Models\Tax', 'id', 'tax_id');
    }

    public function items()
    {
        return $this->hasMany('App\Models\StockTransferNoteItem', 'stn_id', 'id');
    }

    public function store_from()
    {
        return $this->hasOne('App\Models\warehouse', 'id', 'store_from');
    }

    public function store_to()
    {
        return $this->hasOne('App\Models\warehouse', 'id', 'store_to');
    }

    public function fromStore()
    {
        return $this->belongsTo(warehouse::class, 'store_from');
    }

    public function toStore()
    {
        return $this->belongsTo(warehouse::class, 'store_to');
    }

    public function academicSession()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    public function getSubTotal()
    {
        $subTotal = 0;

        foreach ($this->items as $product) {
            $subTotal += ($product->price * $product->quantity);
        }

        return $subTotal;
    }

    public function getTotalTax()
    {
        $totalTax = 0;

        foreach ($this->items as $product) {
            $taxes = Utility::totalTaxRate($product->tax);
            $totalTax += ($taxes / 100) * ($product->price * $product->quantity - $product->discount);
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

    public function getTotal()
    {
        return $this->getSubTotal();
    }

    public function getDue()
    {
        return $this->getTotal();
    }

    public function getInvoiceIdAttribute()
    {
        return $this->attributes['stn_id'] ?? $this->getAttribute('stn_id');
    }

    public function getClassNamesAttribute(): string
    {
        return $this->normalizedStudyPackClasses()->implode(', ');
    }

    public function getPrimaryClassNameAttribute(): string
    {
        return $this->normalizedStudyPackClasses()->first() ?? '';
    }

    protected function normalizedStudyPackClasses(): Collection
    {
        $this->loadMissing('items');

        return $this->items
            ->pluck('study_pack_class')
            ->map(fn ($className) => trim((string) $className))
            ->filter()
            ->unique(function ($className) {
                return strtolower($className);
            })
            ->values();
    }

    public function setInvoiceIdAttribute($value)
    {
        $this->attributes['stn_id'] = $value;
    }

    public static function change_status($invoice_id, $status)
    {
        $invoice = StockTransferNote::find($invoice_id);
        $invoice->status = $status;
        $invoice->update();
    }
}

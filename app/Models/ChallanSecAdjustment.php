<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallanSecAdjustment extends Model
{
    use HasFactory;


    protected $fillable = [
        'roll_no',
        'challan_id',
        'amount',
        'date',
        'voucher_id',
        'owned_by',
        'created_by',
    ];

    public function challan()
    {
        return $this->belongsTo(Challans::class, 'challan_id', 'id');
    }
}

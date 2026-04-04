<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallanHead extends Model
{
    use HasFactory;
    protected $fillable = [
        'challan_id',
        'head_id',
        'price',
        'concession','paid','created_at',
        'updated_at',

    ];

    public function feeHead()
    {
        return $this->belongsTo(FeeHead::class, 'head_id', 'id'); 
    }
    public function challan()
    {
        return $this->belongsTo(Challans::class, 'challan_id', 'id'); 
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LateFeeRemovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'challan_id',
        'challan_no',
        'challan_head_id',
        'old_amount',
        'removed_amount',
        'updated_amount',
        'old_total_amount',
        'new_total_amount',
        'user_id',
        'removal_date',
    ];

    protected $casts = [
        'old_amount' => 'decimal:2',
        'removed_amount' => 'decimal:2',
        'updated_amount' => 'decimal:2',
        'old_total_amount' => 'decimal:2',
        'new_total_amount' => 'decimal:2',
        'removal_date' => 'datetime',
    ];

    public function challan()
    {
        return $this->belongsTo(Challans::class, 'challan_id');
    }

    public function challanHead()
    {
        return $this->belongsTo(ChallanHead::class, 'challan_head_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeHead extends Model
{
    use HasFactory;

    public function account_head()
    {
        return $this->belongsTo('App\Models\ChartOfAccount', 'account_id', 'id');
    }
    public function receivable_head()
    {
        return $this->belongsTo('App\Models\ChartOfAccount', 'receivable_account_id', 'id');
    }
    public function discount_head()
    {
        return $this->belongsTo('App\Models\ChartOfAccount', 'discount_account_id', 'id');
    }
    public function class_wise_head(){
        return $this->belongsTo(ClassWiseFee::class, 'head_id', 'id');
    }
    public function challan_heads()
    {
        return $this->hasMany(ChallanHead::class, 'id', 'head_id'); 
    }
    
}

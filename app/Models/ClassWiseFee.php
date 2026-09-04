<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassWiseFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'class_id',
        'head_id',
        'type',
        'amount',
        'discount',
        'owned_by',
        'created_by',
    ];

    public function feehead()
    {
        return $this->belongsTo('App\Models\FeeHead', 'head_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo('App\Models\ChartOfAccount', 'account_id', 'id');
    }

    public function class()
    {
        return $this->belongsTo('App\Models\Classes', 'class_id', 'id');
    }

    public function session()
    {
        return $this->belongsTo('App\Models\Session', 'session_id', 'id');
    }
}

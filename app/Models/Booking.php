<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'space_id',
        'company_id',
        'user_id',
        'start_date',
        'end_date',
        'total_min',
        'owned_by',
        'created_by',
    ];

    public function company()
    {
        return $this->hasOne('App\Models\Company', 'id', 'company_id');
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
    public function space()
    {
        return $this->hasOne('App\Models\Space', 'id', 'space_id');
    }
}

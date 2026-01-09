<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'capacity',
        'price',
        'type_id',
        'meeting',
        'window',
        'window',
        'description',
        'owned_by',
        'created_by',
    ];

    public function type()
    {
        return $this->hasOne('App\Models\SpaceType', 'id', 'type_id');
    }
}

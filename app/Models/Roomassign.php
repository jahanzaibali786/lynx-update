<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roomassign extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'contract_id',
        'space_id',
        'chair_id',
    ];
    public function space()
    {
        return $this->hasOne('App\Models\Space', 'id', 'space_id');
    }
    public function company()
    {
        return $this->hasOne('App\Models\Company', 'id', 'company_id');
    }
}

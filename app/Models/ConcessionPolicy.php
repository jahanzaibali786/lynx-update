<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConcessionPolicy extends Model
{
    use HasFactory;

    public function concession()
    {
        return $this->hasOne('App\Models\Concession', 'concession_id', 'id');
    }
    public function policy_head()
    {
        return $this->hasMany('App\Models\ConcessionPolicyHead', 'concession_id', 'id');
    }
}

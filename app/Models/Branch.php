<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use LogsActions;
    protected $fillable = [
        'name','owned_by','created_by'
    ];
}

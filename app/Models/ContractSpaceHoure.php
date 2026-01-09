<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractSpaceHoure extends Model
{
    use HasFactory;
    protected $fillable = [
        'space_id','company_id','contract_id','assign_hour','hourly_rate'
    ];
}

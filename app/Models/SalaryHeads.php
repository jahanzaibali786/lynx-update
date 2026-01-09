<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryHeads extends Model
{
    use HasFactory , LogsActions;
    protected $fillable = [
        'head',
        'status',
        'owned_by',
        'created_by',
    ];

    public function finalSettlementHeads()
    {
        return $this->hasMany(EmpFinalSettlementHeads::class, 'head_id');
    }
}

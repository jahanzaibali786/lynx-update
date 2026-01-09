<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpFinalSettlementHeads extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id',
        'final_settlement_id',
        'scale_no',
        'head_id',
        'head_value',
        'earned_value',
    ];
    public function salaryHead()
    {
        return $this->belongsTo(SalaryHeads::class, 'head_id');
    }
}

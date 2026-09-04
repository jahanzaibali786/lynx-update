<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFeeRevisionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'student_fee_structure_id',
        'student_id',
        'reg_id',
        'head_id',
        'percentage',
        'prev_base_amount',
        'new_base_amount',
        'prev_payable_amount',
        'new_payable_amount',
    ];

    public function batch()
    {
        return $this->belongsTo(StudentFeeRevisionBatch::class, 'batch_id');
    }

    public function feehead()
    {
        return $this->belongsTo(FeeHead::class, 'head_id');
    }
}

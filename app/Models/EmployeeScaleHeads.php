<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeScaleHeads extends Model
{
    use HasFactory;
    protected $fillable = [
        'scale_id',
        'scale_no',
        'head',
        'head_value',
        'status',
        'owned_by',
        'created_by',
    ];

    public function scale()
{
        return $this->belongsTo(EmployeeScale::class, 'id', 'scale_id');
}
    public function salaryHeads(){
        return $this->belongsTo(SalaryHeads::class, 'head', 'id');
    }

}

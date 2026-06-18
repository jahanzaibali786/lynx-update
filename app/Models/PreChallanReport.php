<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreChallanReport extends Model
{
    use HasFactory;
    protected $fillable = [
        'branch_id',
        'month',
        'file_path',
        'head_office_file',
        'ho_remarks',
        'remarks',
        'status',
        'owned_by',
        'created_by',

    ];
    public function branch()
    {
        return $this->belongsTo(User::class, 'branch_id');
    }
}

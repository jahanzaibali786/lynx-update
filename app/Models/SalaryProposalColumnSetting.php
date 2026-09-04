<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryProposalColumnSetting extends Model
{
    protected $fillable = [
        'company_id',
        'columns',
        'updated_by',
    ];

    protected $casts = [
        'columns' => 'array',
    ];
}

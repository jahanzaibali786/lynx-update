<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory , LogsActions;
    protected $fillable=[
        'name',
        'active_status',
        'owned_by',
        'created_by',
    ];
    // public function students()
    // {
    //     return $this->hasMany('App\Models\Student', 'section_id', 'id');
    // }
}

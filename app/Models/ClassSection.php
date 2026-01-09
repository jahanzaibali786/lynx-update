<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSection extends Model
{
    use HasFactory;
    protected $fillable = [
        'active_status',
        'class_id',
        'section_id',
        'owned_by',
        'created_by',
    ];
    public function sectionName()
    {
        return $this->belongsTo('App\Models\Section', 'section_id', 'id')->withDefault();
    }
}

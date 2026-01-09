<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'active_status',
        'owned_by',
        'created_by',
    ];

    public function classSection()
    {
      return $this->hasMany('App\Models\ClassSection', 'class_id','id')->with('sectionName');
    }
    public function classhead()
    {
      return $this->hasMany('App\Models\ClassWiseFee', 'class_id','id');
    }
    public function classSectionAll(){
        return $this->belongsToMany('App\Models\Section','class_sections','class_id','section_id');
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\User', 'owned_by','id');
    }
}

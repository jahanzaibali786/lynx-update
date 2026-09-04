<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'class_id',
        'previous_section_id',
        'section_id',
        'date',
        'changed_by',
        'created_by',
        'owned_by',
    ];
    public function student()
    {
        return $this->belongsTo(StudentRegistration::class, 'student_id', 'id');
    }
    public function previousSection()
    {
        return $this->belongsTo(Section::class, 'previous_section_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');   
    }
}

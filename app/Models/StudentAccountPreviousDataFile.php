<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAccountPreviousDataFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'student_id',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'uploaded_by',
        'deleted_by',
        'created_by',
        'finalized_at',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(User::class, 'branch_id');
    }

    public function student()
    {
        return $this->belongsTo(StudentRegistration::class, 'student_id', 'id');
    }
}

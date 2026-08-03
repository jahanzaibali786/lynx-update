<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyPackChallans extends Model
{
    use HasFactory;
    protected $fillable=[
        'total_amount',
        'voucher_id',
    ];
    public function student(){
        return $this->belongsTo(StudentRegistration::class, 'student_id', 'id');
    }
    public function getItems(){
        return $this->hasMany(StudyPackChallanItems::class, 'challan_id', 'id');
    }
     public function class(){
        return $this->belongsTo(Classes::class, 'class_id', 'id');
    }
     public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }
        public function items()
    {
        return $this->hasMany('App\Models\StudyPackChallanItems', 'challan_id', 'id');
    }
}

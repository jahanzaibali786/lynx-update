<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRegistration extends Model
{
    use HasFactory , LogsActions;
    protected $fillable = [
        'regdate',
        'stdname',
        'dob',
        'reg_no',
        'roll_no',
        'religion',
        'gender',
        'prevschool',
        'prevclass',
        'fathername',
        'fathercnic',
        'fatherphone',
        'fathercell',
        'fatherprofession',
        'mothername',
        'mothercnic',
        'motherprofession',
        'email',
        'city',
        'birth_place',
        'district',
        'nationality',
        'address',
        'permanent_address',
        'branch',
        'class_id',
        'reg_class',
        'session_id',
        'active_status',
        'student_status',
        'registrationfee',
        'register_option',
        'custody_relation',
        'custody_name',
        'custody_cnic',
        'reg_type',
        'remarks',
        'owned_by',
        'created_by',
    ];
    public function enrollment()
    {
        return $this->hasOne(StudentEnrollments::class, 'regId', 'id');
    }
    public function siblings()
    {
       //father cnic base match.
       return $this->hasMany(StudentRegistration::class, 'fathercnic', 'fathercnic');
    }
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
    public function regclass()
    {
        return $this->belongsTo(Classes::class, 'reg_class', 'id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }
    public function branches()
    {
        return $this->belongsTo(User::class, 'branch', 'id');
    }
    public function withdrawal()
    {
        return $this->belongsTo(StudentWithdrawal::class, 'id', 'student_id');
    }

    public function concession(){
        return $this->belongsTo(Concession::class, 'id', 'student_id');
    }
    
    public function registeroption(){
        return $this->belongsTo(Registring_option::class, 'register_option', 'id');
    }

    public function fee_structure(){
        return $this->hasMany(StudentFeeStructure::class, 'reg_id', 'id');
    }
    public function branch_name(){
        return $this->belongsTo(SchoolDetails::class, 'owned_by', 'branch_id');
    }
    public function teacherChild(){
        return $this->hasOne(EmpChildrens::class, 'student_id', 'id');
    }
}

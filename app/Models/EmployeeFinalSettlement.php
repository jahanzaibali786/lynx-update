<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeFinalSettlement extends Model
{
    use HasFactory;
    protected $fillable = [
        'branch_id',
        'emp_id',
        'designation_id',
        'doj',
        'tenure',
        'payscale_no',
        'basic_sal',
        'security_amnt',
        'sec_eligibilty',
        'payable',
        'working_days',
        'status',
        'owned_by',
        'created_by',
    ];

    public function employee(){
        return $this->belongsTo('App\Models\Employee','emp_id','id');
    }
    public function finalsettlementHeads()
    {
        return $this->hasMany('App\Models\EmpFinalSettlementHeads', 'final_settlement_id', 'id');
    }
    public function final_set_adj_ded()
    {
        return $this->hasMany('App\Models\EmpFinalSettlementAdjDed', 'final_settlement_id', 'id');
    }
    public function employee_payscale_details()
    {
        return $this->hasMany('App\Models\EmployeePayscaleDetail', 'employee_id', 'emp_id');
    }
    public function resignation()
    {
        return $this->hasOne('App\Models\Resignation', 'employee_id', 'emp_id');
    }

    public function designation()
    {
        return $this->hasOne('App\Models\Designation', 'id', 'designation_id');
    }
}

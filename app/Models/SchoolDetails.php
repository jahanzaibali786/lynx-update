<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolDetails extends Model
{
    use HasFactory,LogsActions;

    protected $fillable = [
        'branch_id',
        'headmaster',
        'name',
        'address',
        'branch_code',
        'eobi_reg_no',
        'pessi_reg_no',
        'phone_no',
        'bank',
        'pessi_values',
        'eobi_values',
    ];


    public function headmaster_name(){
        return $this->belongsTo(User::class, 'headmaster','id');
    }
    public function headmaster_designation(){
        return $this->belongsTo(Employee::class, 'headmaster','user_id');
    }

     public function bankname()
    {
        return $this->belongsTo(BankAccount::class, 'bank','id');
    }
}

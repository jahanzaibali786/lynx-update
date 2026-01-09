<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'client_name',
        'contract_id',
        'subject',
        'value',
        'type',
        'start_date',
        'end_date',
        'description',
        'status',
        'contract_description',
        'company_signature',
        'client_signature',
        'company_id',
        'service_id',
        'service_price',
        'security_deposit_id',
        'security_deposit_price',
        'close_date',
        'owned_by',
        'created_by',
    ];

    public static function status()
    {

        $status = [
            'accept' => 'Accept',
            'decline' => 'Decline',

        ];
        return $status;
    }

    public function clients()
    {
        return $this->hasOne('App\Models\User', 'id', 'client_name');
    }

    public function types()
    {
        return $this->hasOne('App\Models\ContractType', 'id', 'type');
    }
    public static function getContractSummary($contracts)
    {
        $total = 0;

        foreach($contracts as $contract)
        {
            $total += $contract->value;
        }

        return \Auth::user()->priceFormat($total);
    }

    public function projects()
    {
        return $this->hasOne('App\Models\Project', 'id', 'project_id');
    }
    public function files()
    {
        return $this->hasMany('App\Models\Contract_attachment', 'contract_id' , 'id');
    }
    public function notes()
    {
        return $this->hasMany('App\Models\ContractNotes', 'contract_id' , 'id');
    }
    public function comment()
    {
        return $this->hasMany('App\Models\ContractComment', 'contract_id', 'id');
    }
    public function note()
    {
        return $this->hasMany('App\Models\ContractNotes', 'contract_id', 'id');
    }

    public function ContractAttechment()
    {
        return $this->belongsTo('App\Models\Contract_attachment', 'id', 'contract_id');
    }

    public function ContractComment()
    {
        return $this->belongsTo('App\Models\ContractComment', 'id', 'contract_id');
    }

    public function ContractNote()
    {
        return $this->belongsTo('App\Models\ContractNotes', 'id', 'contract_id');
    }

    public function company()
    {
        return $this->hasOne('App\Models\Company', 'id', 'company_id');
    }

    public function contractSpace()
    {
        return $this->hasMany('App\Models\ContractSpaceHoure', 'contract_id', 'id');
    }

    public static function spaceContract($contract , $space)
    {
        return ContractSpaceHoure::where('contract_id', $contract)->where('space_id',$space)->first();
    }

}

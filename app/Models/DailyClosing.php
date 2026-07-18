<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyClosing extends Model
{
    protected $fillable = [
        'from_date',
        'to_date',
        'deposit_date',
        'status',
        'issued_by',
        'received_by',
        'issued_by_id',
        'received_by_id',
        'total_income_received',
        'total_income_deposited',
        'difference',
        'note_5000',
        'note_1000',
        'note_500',
        'note_100',
        'note_50',
        'note_20',
        'note_10',
        'note_5',
        'note_2',
        'note_1',
        'created_by',
        'owned_by',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'deposit_date' => 'date',
        'total_income_received' => 'float',
        'total_income_deposited' => 'float',
        'difference' => 'float',
        'note_5000' => 'integer',
        'note_1000' => 'integer',
        'note_500' => 'integer',
        'note_100' => 'integer',
        'note_50' => 'integer',
        'note_20' => 'integer',
        'note_10' => 'integer',
        'note_5' => 'integer',
        'note_2' => 'integer',
        'note_1' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\User', 'owned_by');
    }

    public function issuedByEmployee()
    {
        return $this->belongsTo('App\Models\Employee', 'issued_by_id');
    }

    public function receivedByEmployee()
    {
        return $this->belongsTo('App\Models\Employee', 'received_by_id');
    }

    /**
     * Compute note totals helper
     */
    public function getNoteAmount($denom)
    {
        $field = 'note_' . $denom;
        return ($this->$field ?? 0) * $denom;
    }
}

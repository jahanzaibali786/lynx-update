<?php

namespace App\Models;

use App\Models\Traits\LogsActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory, LogsActions;

    protected $fillable = [
        'year',
        'title',
        'starting_date',
        'ending_date',
        'active_status',
        'owned_by',
        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Active Session ID
    |--------------------------------------------------------------------------
    */
    public static function activeSessionId()
    {
        return static::where('created_by', auth()->user()->creatorId())
            ->where('active_status', 1)
            ->value('id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlEvent extends Model
{
    protected $fillable = [
        'room_id',
        'device_id',
        'user_id',
        'session_id',
        'command',
        'payload',
        'status',
        'result_message',
        'executed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'executed_at' => 'datetime',
    ];
}

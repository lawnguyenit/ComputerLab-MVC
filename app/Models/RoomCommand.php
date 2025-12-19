<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomCommand extends Model
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
        'dispatched_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'dispatched_at' => 'datetime',
    ];
}

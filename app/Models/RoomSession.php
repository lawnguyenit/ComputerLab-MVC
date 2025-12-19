<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomSession extends Model
{
    protected $fillable = [
        'room_id',
        'user_id',
        'started_at',
        'expires_at',
        'ended_at',
        'status',
        'override',
        'override_reason',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'ended_at' => 'datetime',
        'override' => 'boolean',
    ];
}

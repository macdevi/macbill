<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikrotikPppoeActiveSession extends Model
{
    protected $fillable = [
        'mikrotik_router_id',
        'mikrotik_id',
        'name',
        'service',
        'caller_id',
        'address',
        'uptime',
        'encoding',
        'raw_json',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(MikrotikRouter::class, 'mikrotik_router_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikrotikPppoeSecret extends Model
{
    protected $fillable = [
        'mikrotik_router_id',
        'mikrotik_id',
        'name',
        'password',
        'service',
        'profile',
        'local_address',
        'remote_address',
        'disabled',
        'comment',
        'raw_json',
        'last_synced_at',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'last_synced_at' => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(MikrotikRouter::class, 'mikrotik_router_id');
    }
}

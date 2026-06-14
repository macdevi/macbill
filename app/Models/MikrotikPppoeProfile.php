<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikrotikPppoeProfile extends Model
{
    protected $fillable = [
        'mikrotik_router_id',
        'mikrotik_id',
        'name',
        'local_address',
        'remote_address',
        'rate_limit',
        'only_one',
        'raw_json',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(MikrotikRouter::class, 'mikrotik_router_id');
    }
}

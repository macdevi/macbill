<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikrotikRouter extends Model
{
    protected $fillable = [
        'name',
        'host',
        'api_port',
        'username',
        'api_password',
        'use_ssl',
        'status',
        'notes',
        'last_test_status',
        'last_test_message',
        'last_test_at',
    ];

    protected $casts = [
        'api_port' => 'integer',
        'use_ssl' => 'boolean',
        'last_test_at' => 'datetime',
        'api_password' => 'encrypted',
    ];

    public function pppoeProfiles()
    {
        return $this->hasMany(MikrotikPppoeProfile::class, 'mikrotik_router_id');
    }

    public function pppoeSecrets()
    {
        return $this->hasMany(MikrotikPppoeSecret::class, 'mikrotik_router_id');
    }
}

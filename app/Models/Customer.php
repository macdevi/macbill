<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'odp_id',
        'odp',
        'port_number',
        'package_id',
        'billing_day',
        'monthly_price',
        'status',
        'mikrotik_sync_message',
        'pppoe_uptime',
        'pppoe_caller_id',
        'pppoe_remote_address',
        'pppoe_last_seen_at',
        'pppoe_online_at',
        'pppoe_online_status',
        'mikrotik_synced_at',
        'mikrotik_sync_status',
        'pppoe_password',
        'pppoe_username',
        'mikrotik_pppoe_profile_id',
        'mikrotik_pppoe_secret_id',
        'mikrotik_router_id',
        'cable_distance_m',
        'cable_path_json',
        'longitude',
        'latitude',
    ];

    protected $casts = [
        'pppoe_last_seen_at' => 'datetime',
        'pppoe_online_at' => 'datetime',
        'pppoe_password' => 'encrypted',
        'mikrotik_synced_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'cable_distance_m' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function odpMaster()
    {
        return $this->belongsTo(Odp::class, 'odp_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function invoicesForCollector()
    {
        return $this->hasMany(Invoice::class);
    }

    public function mikrotikRouter()
    {
        return $this->belongsTo(\App\Models\MikrotikRouter::class, 'mikrotik_router_id');
    }

    public function mikrotikPppoeProfile()
    {
        return $this->belongsTo(\App\Models\MikrotikPppoeProfile::class, 'mikrotik_pppoe_profile_id');
    }


    public function mikrotikPppoeSecret()
    {
        return $this->belongsTo(\App\Models\MikrotikPppoeSecret::class, 'mikrotik_pppoe_secret_id');
    }
}

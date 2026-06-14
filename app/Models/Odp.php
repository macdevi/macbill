<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Odp extends Model
{
    protected $fillable = [
        'name',
        'location',
        'port_count',
        'longitude',
        'latitude',
        'status',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}

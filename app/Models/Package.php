<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'speed',
        'status',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}

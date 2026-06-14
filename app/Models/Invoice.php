<?php

namespace App\Models;

use App\Services\SettingService;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{

    protected static function booted(): void
    {
        static::creating(function ($invoice) {
            if (!empty($invoice->invoice_number)) {
                $invoice->invoice_number = SettingService::normalizeInvoiceNumber(
                    $invoice->invoice_number,
                    $invoice->period,
                    $invoice->id
                );
            }
        });
    }

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'package_id',
        'period',
        'due_date',
        'amount',
        'paid_amount',
        'status',
        'paid_at',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

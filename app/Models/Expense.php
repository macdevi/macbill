<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_date',
        'category',
        'description',
        'amount',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_type',
        'amount',
        'payment_method',
        'payment_provider',
        'transaction_id',
        'card_last_four',
        'status',
        'gateway_ref',
        'checksum',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}


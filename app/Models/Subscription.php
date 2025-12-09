<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
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
        'starts_at',
        'ends_at',
    ];

    protected static function booted()
    {
        // Auto-update status when subscription expires (FR-23)
        static::saving(function ($subscription) {
            if ($subscription->ends_at && $subscription->ends_at->isPast() && $subscription->status === 'completed') {
                $subscription->status = 'expired';
            }
        });
    }

    protected $casts = [
        'amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive()
    {
        return $this->status === 'completed' 
            && now()->between($this->starts_at, $this->ends_at);
    }

    public function getPlanNameAttribute()
    {
        return match($this->plan_type) {
            'monthly' => 'Monthly Plan',
            '6months' => '6 Months Plan',
            'yearly' => '1 Year Plan',
            default => 'Unknown Plan',
        };
    }

    public function getPlanDurationAttribute()
    {
        return match($this->plan_type) {
            'monthly' => 1,
            '6months' => 6,
            'yearly' => 12,
            default => 0,
        };
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
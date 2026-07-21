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

    /** Canonical plan catalog — keys match names and durations. */
    public static function planCatalog(): array
    {
        return [
            'weekly' => [
                'name' => 'Weekly Pass',
                'price' => 350,
                'days' => 7,
                'desc' => 'Unlimited rides for 7 days',
                'tag' => 'Most Popular',
            ],
            'monthly' => [
                'name' => 'Monthly Pass',
                'price' => 1200,
                'days' => 30,
                'desc' => 'Best for regular commuters',
                'tag' => 'Best Value',
            ],
            'single' => [
                'name' => 'Single Ride',
                'price' => 30,
                'days' => 1,
                'desc' => 'Pay as you go',
                'tag' => null,
            ],
        ];
    }

    public function isActive()
    {
        return $this->status === 'completed' 
            && now()->between($this->starts_at, $this->ends_at);
    }

    /** Paid subscriptions that are still within their validity window. */
    public function scopeCurrentlyActive($query)
    {
        $now = now();

        return $query->where('status', 'completed')
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>', $now);
    }

    public function getPlanNameAttribute()
    {
        $catalog = self::planCatalog();
        if (isset($catalog[$this->plan_type]['name'])) {
            return $catalog[$this->plan_type]['name'];
        }

        // Legacy keys (pre-rename) — keep readable if any row was not migrated
        return match ($this->plan_type) {
            '6months' => $catalog['monthly']['name'] ?? 'Monthly Pass',
            'yearly' => $catalog['single']['name'] ?? 'Single Ride',
            default => 'Unknown Plan',
        };
    }

    public function getPlanDurationAttribute()
    {
        $catalog = self::planCatalog();
        if (isset($catalog[$this->plan_type]['days'])) {
            return (int) $catalog[$this->plan_type]['days'];
        }

        return match ($this->plan_type) {
            '6months' => (int) ($catalog['monthly']['days'] ?? 30),
            'yearly' => (int) ($catalog['single']['days'] ?? 1),
            default => 0,
        };
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
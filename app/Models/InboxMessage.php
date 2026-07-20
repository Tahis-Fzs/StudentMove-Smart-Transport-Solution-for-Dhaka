<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboxMessage extends Model
{
    use HasFactory;

    public const TYPE_DELAY = 'delay';
    public const TYPE_ARRIVAL = 'arrival';
    public const TYPE_BOOKING = 'booking';
    public const TYPE_ANNOUNCEMENT = 'announcement';
    public const TYPE_SYSTEM = 'system';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'icon',
        'meta',
        'read_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function markRead(): void
    {
        if ($this->read_at === null) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    public function iconClass(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        return match ($this->type) {
            self::TYPE_DELAY => 'bi-exclamation-triangle',
            self::TYPE_ARRIVAL => 'bi-bus-front',
            self::TYPE_BOOKING => 'bi-ticket-perforated',
            self::TYPE_ANNOUNCEMENT => 'bi-megaphone',
            default => 'bi-bell',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatThread extends Model
{
    public const TYPE_ASSISTANT = 'assistant';
    public const TYPE_SUPPORT = 'support';

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'subject',
        'last_message_at',
        'admin_read_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'admin_read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_ASSISTANT => 'AI Assistant',
            self::TYPE_SUPPORT => 'Support',
            default => ucfirst($this->type),
        };
    }

    public function hasUnreadForAdmin(): bool
    {
        if ($this->type !== self::TYPE_SUPPORT) {
            return false;
        }

        $since = $this->admin_read_at ?? $this->created_at;

        return $this->messages()
            ->where('role', ChatMessage::ROLE_USER)
            ->where('created_at', '>', $since)
            ->exists();
    }

    /** Get or create the user's thread for a channel. */
    public static function forUser(User $user, string $type): self
    {
        return self::firstOrCreate(
            ['user_id' => $user->id, 'type' => $type],
            [
                'status' => self::STATUS_OPEN,
                'subject' => $type === self::TYPE_ASSISTANT
                    ? 'StudentMove Assistant'
                    : 'Support request',
            ]
        );
    }
}

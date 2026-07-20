<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'chat_thread_id',
        'role',
        'body',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(ChatThread::class, 'chat_thread_id');
    }

    public function senderLabel(): string
    {
        return match ($this->role) {
            self::ROLE_USER => 'You',
            self::ROLE_ASSISTANT => 'Assistant',
            self::ROLE_ADMIN => 'Support',
            default => ucfirst($this->role),
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmailVerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'email',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Generate a 6-digit verification code
     */
    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if code is valid and not expired
     */
    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }

    /**
     * Mark code as used
     */
    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }

    /**
     * Relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to find valid codes
     */
    public function scopeValid($query)
    {
        return $query->where('used', false)
                     ->where('expires_at', '>', now());
    }
}

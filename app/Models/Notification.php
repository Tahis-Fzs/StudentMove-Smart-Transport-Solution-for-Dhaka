<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory;

    public const AUDIENCE_ALL = 'all';
    public const AUDIENCE_UNIVERSITY = 'university';
    public const AUDIENCE_DEPARTMENT = 'department';
    public const AUDIENCE_ROUTE = 'route';

    protected $fillable = [
        'title',
        'message',
        'icon',
        'icon_color',
        'type',
        'audience',
        'target_value',
        'is_active',
        'sort_order',
        'published_at',
        'expires_at',
        'offer_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Within publish / expire window (null = open-ended). */
    public function scopeScheduled(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', $now);
            });
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function audienceLabel(): string
    {
        $audience = $this->audience ?: self::AUDIENCE_ALL;

        if ($audience === self::AUDIENCE_ALL) {
            return 'Everyone';
        }

        $target = trim((string) $this->target_value);

        return match ($audience) {
            self::AUDIENCE_UNIVERSITY => 'University: ' . ($target ?: '—'),
            self::AUDIENCE_DEPARTMENT => 'Department: ' . ($target ?: '—'),
            self::AUDIENCE_ROUTE => 'Route: ' . ($target ?: '—'),
            default => ucfirst($audience),
        };
    }

    public function isWithinSchedule(): bool
    {
        $now = now();

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /** Whether this announcement should be shown to the given user. */
    public function matchesUser(?User $user): bool
    {
        $audience = $this->audience ?: self::AUDIENCE_ALL;

        if ($audience === self::AUDIENCE_ALL) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $target = trim((string) $this->target_value);
        if ($target === '') {
            return false;
        }

        return match ($audience) {
            self::AUDIENCE_UNIVERSITY => $this->matchesUniversity($user, $target),
            self::AUDIENCE_DEPARTMENT => $this->matchesText($user->department, $target),
            self::AUDIENCE_ROUTE => $this->matchesRoute($user, $target),
            default => true,
        };
    }

    /**
     * Active + scheduled announcements visible to this user (Flutter-style targeting).
     */
    public static function visibleFor(?User $user, ?int $limit = null): Collection
    {
        $items = static::query()
            ->with('offer')
            ->active()
            ->scheduled()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn (self $n) => $n->matchesUser($user))
            ->values();

        return $limit !== null ? $items->take($limit)->values() : $items;
    }

    protected function matchesUniversity(User $user, string $target): bool
    {
        $uni = trim((string) $user->university);
        if ($uni === '') {
            return false;
        }

        if ($this->matchesText($uni, $target)) {
            return true;
        }

        $catalog = University::query()
            ->where(function (Builder $q) use ($uni) {
                $q->whereRaw('LOWER(name) = ?', [Str::lower($uni)])
                    ->orWhereRaw('LOWER(short_name) = ?', [Str::lower($uni)]);
            })
            ->first();

        if (!$catalog) {
            return false;
        }

        return $this->matchesText($catalog->name, $target)
            || $this->matchesText($catalog->short_name, $target);
    }

    protected function matchesRoute(User $user, string $target): bool
    {
        $needle = Str::lower(trim($target));
        if ($needle === '') {
            return false;
        }

        // Match in SQL (case-insensitive) against individual fields — avoids loading
        // every saved route into memory and accidental cross-field concatenations.
        return $user->savedRoutes()
            ->where(function (Builder $q) use ($needle) {
                $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $needle) . '%';
                $q->whereRaw('LOWER(origin) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(destination) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(title) LIKE ?', [$like])
                    ->orWhereRaw("LOWER(CONCAT(COALESCE(origin,''), ' to ', COALESCE(destination,''))) LIKE ?", [$like]);
            })
            ->exists();
    }

    protected function matchesText(?string $value, string $target): bool
    {
        $value = trim((string) $value);
        if ($value === '') {
            return false;
        }

        return Str::lower($value) === Str::lower($target)
            || str_contains(Str::lower($value), Str::lower($target))
            || str_contains(Str::lower($target), Str::lower($value));
    }
}

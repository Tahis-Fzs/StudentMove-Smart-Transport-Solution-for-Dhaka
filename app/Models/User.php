<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'university',
        'student_id',
        'date_of_birth',
        'department',
        'faculty',
        'year_of_study',
        'semester',
        'semester_system',
        'current_address',
        'home_address',
        'preferred_language',
        'profile_image',
        'is_admin',
        'is_banned',
        'bus_delay_notifications',
        'route_change_alerts',
        'promotional_offers',
        'firebase_uid',
        'auth_provider',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
            'bus_delay_notifications' => 'boolean',
            'route_change_alerts' => 'boolean',
            'promotional_offers' => 'boolean',
        ];
    }

    
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->currentlyActive()
            ->latest();
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function savedRoutes()
    {
        return $this->hasMany(SavedRoute::class)->latest();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class)->latest();
    }

    public function inboxMessages()
    {
        return $this->hasMany(InboxMessage::class)->latest();
    }

    public function chatThreads()
    {
        return $this->hasMany(ChatThread::class);
    }

    /**
     * Users eligible for fallback delay alerts when nobody booked the bus today.
     * Must group route matching as (no favorites OR matching favorite) — never OR at query root
     * or users with saved routes bypass the notification preference check.
     */
    public function scopeEligibleForDelayFallback(Builder $query, ?string $routeName = null): Builder
    {
        $query->where(function ($q) {
            $q->where('bus_delay_notifications', true)
                ->orWhereNull('bus_delay_notifications');
        });

        $needle = strtolower(trim((string) $routeName));

        $query->where(function ($q) use ($needle) {
            if ($needle === '') {
                $q->whereDoesntHave('savedRoutes');

                return;
            }

            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $needle) . '%';

            $q->where(function ($routeQ) use ($like) {
                $routeQ->whereDoesntHave('savedRoutes')
                    ->orWhereHas('savedRoutes', function ($sq) use ($like) {
                        $sq->where(function ($match) use ($like) {
                            $match->whereRaw('LOWER(origin) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(destination) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(title) LIKE ?', [$like]);
                        });
                    });
            });
        });

        return $query;
    }

    /** Public URL for the user's avatar (upload → Firebase/Google → placeholder). */
    public function avatarUrl(): string
    {
        if ($this->profile_image) {
            $path = '/storage/' . ltrim($this->profile_image, '/');
            $version = optional($this->updated_at)->timestamp ?? time();

            return $path . '?v=' . $version;
        }

        if (!empty($this->avatar_url)) {
            return $this->avatar_url;
        }

        return $this->avatarPlaceholderUrl();
    }

    /**
     * Next avatar when avatarUrl() fails in the browser.
     * Never fall back to Google after a custom upload — use initials instead.
     */
    public function avatarFallbackUrl(): string
    {
        return $this->avatarPlaceholderUrl();
    }

    public function avatarPlaceholderUrl(): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?: 'User') . '&background=0b6e6a&color=fff';
    }
}

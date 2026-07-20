<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
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
        'year_of_study',
        'current_address',
        'home_address',
        'preferred_language',
        'profile_image',
        'is_admin',
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
            ->where('status', 'completed')
            ->where('ends_at', '>', now())
            ->latest();
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }
}

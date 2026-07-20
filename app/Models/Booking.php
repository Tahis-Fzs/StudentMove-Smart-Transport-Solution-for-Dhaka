<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'user_id',
        'bus_schedule_id',
        'booking_code',
        'route_name',
        'bus_number',
        'origin',
        'destination',
        'travel_date',
        'departure_time',
        'seats',
        'seat_preference',
        'fare',
        'status',
        'notes',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'fare' => 'decimal:2',
        'seats' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function busSchedule(): BelongsTo
    {
        return $this->belongsTo(BusSchedule::class);
    }

    public function isCancellable(): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true)) {
            return false;
        }

        return $this->travel_date->isToday() || $this->travel_date->isFuture();
    }

    public function pathLabel(): string
    {
        return $this->origin . ' → ' . $this->destination;
    }

    public static function generateCode(): string
    {
        do {
            $code = 'SM' . Str::upper(Str::random(8));
        } while (static::where('booking_code', $code)->exists());

        return $code;
    }

    /** Seats already held (pending/confirmed) for a schedule on a date. */
    public static function seatsTaken(?int $busScheduleId, string $travelDate): int
    {
        // Strict null check: id 0 must not be treated as a demo/empty schedule.
        if ($busScheduleId === null) {
            return 0;
        }

        return (int) static::query()
            ->where('bus_schedule_id', $busScheduleId)
            ->whereDate('travel_date', $travelDate)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED])
            ->sum('seats');
    }
}

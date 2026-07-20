<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusSchedule extends Model
{
    use HasFactory;

    public const DAY_KEYS = ['sat', 'sun', 'mon', 'tue', 'wed', 'thu'];

    protected $fillable = [
        'route_name',
        'departure_time',
        'run_days',
        'schedule_note',
        'university_tags',
        'departure_location',
        'arrival_location',
        'bus_number',
        'price',
        'seats_total',
        'is_active',
        'current_lat',
        'current_lng',
        'location_updated_at',
        'heading',
        'speed_kmh',
        'status',
        'delay_minutes',
    ];

    protected $casts = [
        'location_updated_at' => 'datetime',
        'is_active' => 'boolean',
        'speed_kmh' => 'float',
        'current_lat' => 'float',
        'current_lng' => 'float',
        'run_days' => 'array',
        'university_tags' => 'array',
    ];

    /** @return list<string> */
    public function runDays(): array
    {
        $days = $this->run_days;

        if (!is_array($days) || $days === []) {
            return self::DAY_KEYS;
        }

        return array_values(array_intersect(
            array_map('strtolower', $days),
            self::DAY_KEYS
        ));
    }

    public function runsOn(string $day): bool
    {
        return in_array(strtolower($day), $this->runDays(), true);
    }

    /** @return list<string> */
    public function universityTags(): array
    {
        $tags = $this->university_tags;

        if (!is_array($tags)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($tag) => trim((string) $tag),
            $tags
        )));
    }

    public function routePathLabel(): string
    {
        if ($this->departure_location && $this->arrival_location) {
            return $this->departure_location . ' → ' . $this->arrival_location;
        }

        return (string) ($this->route_name ?: 'Route');
    }

    public function displayTimeLabel(): string
    {
        $raw = trim((string) ($this->departure_time ?: ''));

        if ($raw === '') {
            return '—';
        }

        try {
            return Carbon::parse($raw)->format('g.i A');
        } catch (\Throwable) {
            return $raw;
        }
    }

    public function displayDateLabel(): string
    {
        return now()->format('j M');
    }

    /** Normalize run-day input from admin forms. */
    public static function normalizeRunDays(?array $days): array
    {
        if (!$days) {
            return self::DAY_KEYS;
        }

        $clean = array_values(array_unique(array_intersect(
            array_map('strtolower', $days),
            self::DAY_KEYS
        )));

        return $clean === [] ? self::DAY_KEYS : $clean;
    }

    /** @return list<string> */
    public static function parseUniversityTags(?string $input): array
    {
        if ($input === null || trim($input) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/[,;|]+/', $input) ?: []
        )));
    }
}

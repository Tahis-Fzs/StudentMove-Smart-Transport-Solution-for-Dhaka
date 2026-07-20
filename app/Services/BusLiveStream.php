<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/** Push live bus GPS snapshots to map clients via SSE (Laravel 9 — no Reverb required). */
class BusLiveStream
{
    public static function cacheKey(int $busId): string
    {
        return 'bus_live_stream:' . $busId;
    }

    public static function seqKey(int $busId): string
    {
        return self::cacheKey($busId) . ':seq';
    }

    public static function publish(int $busId, array $payload): void
    {
        $seq = (int) Cache::get(self::seqKey($busId), 0) + 1;
        $payload['_seq'] = $seq;
        $payload['bus_id'] = $busId;
        $payload['pushed_at'] = now()->toIso8601String();

        Cache::put(self::cacheKey($busId), $payload, now()->addMinutes(15));
        Cache::put(self::seqKey($busId), $seq, now()->addMinutes(15));
    }

    public static function read(int $busId): ?array
    {
        $payload = Cache::get(self::cacheKey($busId));

        return is_array($payload) ? $payload : null;
    }

    /** @param array<string, mixed> $gps lat/lng/heading/speed from driver ping */
    public static function publishGps(int $busId, array $gps): void
    {
        self::publish($busId, array_merge([
            'has_gps' => true,
            'gps_fresh' => true,
            'gps_stale' => false,
            'gps_age_seconds' => 0,
        ], $gps));
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BusSchedule;
use App\Models\SavedRoute;
use App\Models\User;
use App\Services\InboxService;
use App\Services\BusLiveStream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BusRouteController extends Controller
{
    public function __construct(protected InboxService $inbox)
    {
    }
    // Backend Logic for FR-9 & FR-16
    public function index()
    {
        $demos = collect([
            (object) [
                'id' => 1,
                'route_name' => 'Uttara to DSC',
                'bus_number' => 'Demo-1',
                'departure_time' => '07:00',
                'departure_location' => 'Uttara',
                'arrival_location' => 'DSC',
                'current_lat' => null,
                'current_lng' => null,
                'run_days' => ['sun', 'mon', 'tue', 'wed', 'thu'],
                'schedule_note' => 'Morning peak — allow extra 10 min through Kuril.',
                'university_tags' => ['DIU', 'DSC'],
            ],
            (object) [
                'id' => 2,
                'route_name' => 'Rajlakshmi to Mirpur',
                'bus_number' => 'Demo-2',
                'departure_time' => '09:00',
                'departure_location' => 'Rajlakshmi',
                'arrival_location' => 'Mirpur',
                'current_lat' => null,
                'current_lng' => null,
                'run_days' => ['sun', 'tue', 'thu', 'sat'],
                'schedule_note' => 'Board at Rajlakshmi main gate · student ID required.',
                'university_tags' => ['DIU', 'BUET'],
            ],
            (object) [
                'id' => 3,
                'route_name' => 'Rajlakshmi to Gulshan',
                'bus_number' => 'Demo-3',
                'departure_time' => '12:00',
                'departure_location' => 'Rajlakshmi',
                'arrival_location' => 'Gulshan',
                'current_lat' => null,
                'current_lng' => null,
                'run_days' => ['mon', 'wed', 'sat'],
                'schedule_note' => 'Lunch corridor — limited stops after Banani.',
                'university_tags' => ['NSU', 'BRAC'],
            ],
        ]);

        $buses = BusSchedule::query()
            ->orderBy('departure_time')
            ->take(3)
            ->get();

        while ($buses->count() < 3) {
            $buses->push($demos[$buses->count()]);
        }

        $scheduleCards = $buses->take(3)->values()->map(fn ($bus) => $this->scheduleCard($bus));

        return view('next-bus-arrival', compact('scheduleCards'));
    }

    /** Normalize a DB model or demo object for the live-map cards. */
    protected function scheduleCard(mixed $bus): object
    {
        if ($bus instanceof BusSchedule) {
            return (object) [
                'id' => $bus->id,
                'live_stream' => true,
                'route_name' => $bus->route_name,
                'bus_number' => $bus->bus_number,
                'departure_time' => $bus->departure_time,
                'current_lat' => $bus->current_lat,
                'current_lng' => $bus->current_lng,
                'run_days' => $bus->runDays(),
                'schedule_note' => $bus->schedule_note,
                'university_tags' => $bus->universityTags(),
                'display_time' => $bus->displayTimeLabel(),
                'display_date' => $bus->displayDateLabel(),
                'path_label' => $bus->routePathLabel(),
            ];
        }

        $runDays = $bus->run_days ?? BusSchedule::DAY_KEYS;
        if (!is_array($runDays)) {
            $runDays = BusSchedule::DAY_KEYS;
        }

        $tags = $bus->university_tags ?? [];
        if (!is_array($tags)) {
            $tags = [];
        }

        $path = ($bus->departure_location ?? null) && ($bus->arrival_location ?? null)
            ? $bus->departure_location . ' → ' . $bus->arrival_location
            : ($bus->route_name ?? 'Route');

        $timeLabel = '—';
        if (!empty($bus->departure_time)) {
            try {
                $timeLabel = \Carbon\Carbon::parse($bus->departure_time)->format('g.i A');
            } catch (\Throwable) {
                $timeLabel = (string) $bus->departure_time;
            }
        }

        return (object) [
            'id' => $bus->id ?? null,
            'live_stream' => false,
            'route_name' => $bus->route_name ?? 'Route',
            'bus_number' => $bus->bus_number ?? null,
            'departure_time' => $bus->departure_time ?? null,
            'current_lat' => $bus->current_lat ?? null,
            'current_lng' => $bus->current_lng ?? null,
            'run_days' => array_values(array_intersect(array_map('strtolower', $runDays), BusSchedule::DAY_KEYS)),
            'schedule_note' => $bus->schedule_note ?? null,
            'university_tags' => array_values(array_filter(array_map('trim', $tags))),
            'display_time' => $timeLabel,
            'display_date' => now()->format('j M'),
            'path_label' => $path,
        ];
    }

    // Backend Logic for FR-13 (Route Suggestion) + saved favorites
    public function suggest(Request $request): View
    {
        $destination = $request->input('destination');
        $savedRoutes = Auth::check()
            ? Auth::user()->savedRoutes()->limit(20)->get()
            : collect();

        return view('route-suggestion', compact('destination', 'savedRoutes'));
    }

    /** FR-17: Persist a favorite route (matches Flutter savedRoutes preference). */
    public function saveFavorite(Request $request): JsonResponse
    {
        $data = $request->validate([
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:120'],
            'duration_label' => ['nullable', 'string', 'max:40'],
            'cost_label' => ['nullable', 'string', 'max:40'],
            'transfers' => ['nullable', 'integer', 'min:0', 'max:20'],
            'buses' => ['nullable', 'array'],
            'buses.*' => ['string', 'max:80'],
            'description' => ['nullable', 'string', 'max:1000'],
            'comfort' => ['nullable', 'string', 'max:40'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
        ]);

        $route = SavedRoute::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'origin' => $data['origin'],
                'destination' => $data['destination'],
                'title' => $data['title'] ?? 'Saved route',
            ],
            [
                'duration_label' => $data['duration_label'] ?? null,
                'cost_label' => $data['cost_label'] ?? null,
                'transfers' => $data['transfers'] ?? null,
                'buses' => $data['buses'] ?? null,
                'description' => $data['description'] ?? null,
                'comfort' => $data['comfort'] ?? null,
                'rating' => $data['rating'] ?? null,
            ]
        );

        return response()->json([
            'ok' => true,
            'message' => 'Route saved to your favorites.',
            'route' => [
                'id' => $route->id,
                'origin' => $route->origin,
                'destination' => $route->destination,
                'title' => $route->title,
                'path' => $route->pathLabel(),
                'duration_label' => $route->duration_label,
                'cost_label' => $route->cost_label,
            ],
        ]);
    }

    public function destroyFavorite(SavedRoute $savedRoute): RedirectResponse
    {
        abort_unless($savedRoute->user_id === Auth::id(), 403);
        $savedRoute->delete();

        return back()->with('success', 'Saved route removed.');
    }

    // 🚀 FR-11: DRIVER SIDE - Updates the location (by bus_id)
    public function updateLocation(Request $request)
    {
        $bus = BusSchedule::where('id', $request->bus_id)->first();
        
        if ($bus) {
            $bus->update([
                'current_lat' => $request->lat,
                'current_lng' => $request->lng,
                'location_updated_at' => now(),
            ]);

            BusLiveStream::publishGps((int) $bus->id, [
                'lat' => (float) $request->lat,
                'lng' => (float) $request->lng,
                'heading' => $bus->heading !== null && $bus->heading !== '' ? (float) $bus->heading : null,
                'speed_kmh' => $bus->speed_kmh !== null ? (float) $bus->speed_kmh : null,
                'location_updated_at' => now()->toIso8601String(),
            ]);

            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'error'], 404);
    }

    // 🚀 FR-12 & FR-15: Fetch Location AND Calculate Delay AND Send ETA
    public function getBusLocation($id)
    {
        $bus = BusSchedule::find($id);

        if (!$bus) {
            return response()->json(['error' => 'Bus not found'], 404);
        }

        $gpsAgeSeconds = null;
        $gpsFresh = false;
        // Defaults on the table mean lat/lng are rarely null — require a real driver ping.
        $hasFix = $bus->current_lat !== null
            && $bus->current_lng !== null
            && $bus->location_updated_at !== null;
        if ($hasFix) {
            $gpsAgeSeconds = max(0, (int) $bus->location_updated_at->diffInSeconds(now()));
            // Fresh if pinged within the last 45 seconds
            $gpsFresh = $gpsAgeSeconds <= 45;
        }

        $heading = $bus->heading !== null && $bus->heading !== ''
            ? (float) $bus->heading
            : null;
        $speedKmh = $bus->speed_kmh !== null ? (float) $bus->speed_kmh : null;

        // 1. Calculate expected arrival time based on schedule
        $now = \Carbon\Carbon::now();
        try {
            $departureTime = \Carbon\Carbon::parse($bus->departure_time ?: '07:00');
        } catch (\Throwable $e) {
            report($e);
            $departureTime = $now->copy()->startOfDay()->setTime(7, 0);
        }

        // Expected arrival time (assuming 30 minutes travel time from departure)
        $expectedArrivalTime = $departureTime->copy()->addMinutes(30);
        
        // 2. Calculate current ETA based on distance and speed
        $distanceKm = 5.0;
        $normalSpeedKmh = 30.0;
        // Prefer live GPS speed when moving; fall back to schedule/delay model
        $currentSpeedKmh = ($speedKmh !== null && $speedKmh >= 1)
            ? max(5.0, min(80.0, $speedKmh))
            : $normalSpeedKmh;
        
        if ($bus->status === 'delayed' && ($speedKmh === null || $speedKmh < 1)) {
            $currentSpeedKmh = max(10.0, $normalSpeedKmh * 0.5);
        }
        
        $currentEtaMinutes = ($distanceKm / $currentSpeedKmh) * 60;
        $expectedEtaMinutes = ($distanceKm / $normalSpeedKmh) * 60; // Expected ETA at normal speed
        
        // 3. DYNAMIC DELAY CALCULATION: Compare expected vs actual arrival
        $actualArrivalTime = $now->copy()->addMinutes($currentEtaMinutes);
        $delayMinutes = max(0, $actualArrivalTime->diffInMinutes($expectedArrivalTime));
        
        // Also check if current speed is significantly slower than expected
        if ($currentSpeedKmh < $normalSpeedKmh * 0.7) {
            $speedBasedDelay = round(($expectedEtaMinutes - $currentEtaMinutes));
            $delayMinutes = max($delayMinutes, $speedBasedDelay);
        }
        
        // 4. Determine if bus is delayed (threshold: 3+ minutes)
        $isDelayed = $delayMinutes >= 3;
        $wasDelayed = $bus->status === 'delayed';
        
        // 5. Update bus status in database if delay detected
        if ($isDelayed && !$wasDelayed) {
            $bus->update([
                'status' => 'delayed',
                'delay_minutes' => round($delayMinutes)
            ]);
            try {
                $this->notifyDelayInbox($bus, (int) round($delayMinutes));
            } catch (\Throwable $e) {
                report($e);
            }
        } elseif (!$isDelayed && $wasDelayed) {
            $bus->update([
                'status' => 'on_time',
                'delay_minutes' => 0
            ]);
            try {
                $this->notifyArrivalInbox($bus);
            } catch (\Throwable $e) {
                report($e);
            }
        } else {
            $bus->update(['delay_minutes' => round($delayMinutes)]);
        }
        
        // 6. Calculate total ETA (expected + delay)
        $totalMinutes = round($currentEtaMinutes);
        
        // 7. Generate delay message
        $delayMsg = null;
        if ($isDelayed) {
            $reason = $bus->status === 'delayed' ? 'traffic congestion' : 'slower than expected speed';
            $delayMsg = "Bus is delayed by {$delayMinutes} minutes due to {$reason}. Expected arrival: " . 
                       $expectedArrivalTime->format('h:i A') . ", Actual arrival: " . 
                       $actualArrivalTime->format('h:i A');
        }
        
        return response()->json([
            'lat' => $hasFix ? (float) $bus->current_lat : null,
            'lng' => $hasFix ? (float) $bus->current_lng : null,
            'has_gps' => $hasFix,
            'location_updated_at' => $bus->location_updated_at?->toIso8601String(),
            'gps_age_seconds' => $gpsAgeSeconds,
            'gps_fresh' => $gpsFresh,
            'gps_stale' => $hasFix && !$gpsFresh,
            'heading' => $hasFix ? $heading : null,
            'speed_kmh' => $hasFix && $speedKmh !== null ? round($speedKmh, 1) : null,
            'is_delayed' => $isDelayed,
            'delay_minutes' => round($delayMinutes),
            'expected_eta' => round($expectedEtaMinutes) . " mins",
            'current_eta' => round($currentEtaMinutes) . " mins",
            'eta_text' => round($totalMinutes) . " mins",
            'status' => $isDelayed ? 'delayed' : 'on_time',
            'status_msg' => $isDelayed ? "Delayed by " . round($delayMinutes) . " min" : "On Time",
            'delay_msg' => $delayMsg,
            'expected_arrival_time' => $expectedArrivalTime->toIso8601String(),
            'actual_arrival_time' => $actualArrivalTime->toIso8601String(),
            'current_speed' => round($currentSpeedKmh, 1),
            'normal_speed' => round($normalSpeedKmh, 1)
        ]);
    }

    // 🚀 FR-15: Check for Delays (Logic)
    public function checkDelays($id)
    {
        $bus = BusSchedule::find($id);
        
        if (!$bus) {
            return response()->json(['alert' => false, 'message' => 'Bus not found'], 404);
        }

        // If delay is more than 0 minutes, trigger alert
        if ($bus->delay_minutes > 0) {
            return response()->json([
                'alert' => true,
                'message' => "Bus is delayed by {$bus->delay_minutes} minutes due to traffic."
            ]);
        }
        
        return response()->json(['alert' => false]);
    }

    /** Push delay alerts into personal inboxes for riders booked on this bus today. */
    protected function notifyDelayInbox(BusSchedule $bus, int $delayMinutes): void
    {
        $title = ($bus->bus_number ?: 'Bus') . ' delayed';
        $body = ($bus->route_name ?: 'Your route') . ' is delayed by about '
            . $delayMinutes . ' minute' . ($delayMinutes === 1 ? '' : 's') . '.';

        $userIds = Booking::query()
            ->where('bus_schedule_id', $bus->id)
            ->whereDate('travel_date', now()->toDateString())
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->pluck('user_id')
            ->unique();

        if ($userIds->isEmpty()) {
            // Fallback when nobody booked today: notify opted-in users who either
            // (a) saved a matching route, or (b) have delay alerts on but no favorites yet.
            // Requiring savedRoutes alone excluded everyone without favorites.
            $needle = strtolower(trim((string) $bus->route_name));

            User::query()
                ->where(function ($q) {
                    $q->where('bus_delay_notifications', true)
                        ->orWhereNull('bus_delay_notifications');
                })
                ->where(function ($q) use ($needle) {
                    // (no favorites) OR (has a favorite matching this route name)
                    $q->whereDoesntHave('savedRoutes');

                    if ($needle !== '') {
                        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $needle) . '%';
                        $q->orWhereHas('savedRoutes', function ($sq) use ($like) {
                            $sq->where(function ($match) use ($like) {
                                $match->whereRaw('LOWER(origin) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(destination) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(title) LIKE ?', [$like]);
                            });
                        });
                    }
                })
                ->limit(50)
                ->get()
                ->each(fn (User $u) => $this->inbox->pushDelayOnce($u, (int) $bus->id, $title, $body));

            return;
        }

        User::whereIn('id', $userIds)->get()->each(
            fn (User $u) => $this->inbox->pushDelayOnce($u, (int) $bus->id, $title, $body)
        );
    }

    protected function notifyArrivalInbox(BusSchedule $bus): void
    {
        $title = ($bus->bus_number ?: 'Bus') . ' back on time';
        $body = ($bus->route_name ?: 'Your route') . ' is moving again — ETA updated on the live map.';

        Booking::query()
            ->where('bus_schedule_id', $bus->id)
            ->whereDate('travel_date', now()->toDateString())
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->with('user')
            ->get()
            ->each(function (Booking $booking) use ($title, $body, $bus) {
                if ($booking->user) {
                    $this->inbox->pushArrival($booking->user, $title, $body, [
                        'bus_id' => $bus->id,
                        'booking_id' => $booking->id,
                    ]);
                }
            });
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveBusController extends Controller
{
    /** Public live bus feed for Flutter / web clients. */
    public function index(): JsonResponse
    {
        $buses = BusSchedule::query()
            ->where('is_active', true)
            ->orderBy('bus_number')
            ->get()
            ->map(fn (BusSchedule $b) => $this->payload($b));

        return response()->json([
            'ok' => true,
            'server_time' => now()->toIso8601String(),
            'buses' => $buses,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $bus = BusSchedule::find($id);
        if (! $bus) {
            return response()->json(['ok' => false, 'error' => 'Bus not found'], 404);
        }

        return response()->json([
            'ok' => true,
            'server_time' => now()->toIso8601String(),
            'bus' => $this->payload($bus),
        ]);
    }

    /** Driver / device GPS ping (requires Sanctum token). */
    public function updateLocation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bus_id' => ['required', 'integer', 'exists:bus_schedules,id'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'heading' => ['nullable', 'numeric'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);

        $bus = BusSchedule::findOrFail($data['bus_id']);
        $bus->update([
            'current_lat' => $data['lat'],
            'current_lng' => $data['lng'],
            'heading' => $data['heading'] ?? $bus->heading,
            'status' => $data['status'] ?? $bus->status,
            'location_updated_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'bus' => $this->payload($bus->fresh()),
        ]);
    }

    private function payload(BusSchedule $bus): array
    {
        $updated = $bus->location_updated_at;
        $age = $updated ? now()->diffInSeconds($updated) : null;

        return [
            'id' => $bus->id,
            'bus_number' => $bus->bus_number,
            'route_name' => $bus->route_name,
            'status' => $bus->status,
            'lat' => $bus->current_lat !== null ? (float) $bus->current_lat : null,
            'lng' => $bus->current_lng !== null ? (float) $bus->current_lng : null,
            'heading' => $bus->heading,
            'location_updated_at' => $updated?->toIso8601String(),
            'location_age_seconds' => $age,
            'is_stale' => $age === null || $age > 120,
        ];
    }
}

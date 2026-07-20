<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\BusSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\BusLiveStream;

class DriverController extends Controller
{
    public function dashboard()
    {
        $busId = Session::get('bus_id');
        if (!$busId) {
            return redirect()->route('driver.login');
        }

        $bus = BusSchedule::findOrFail($busId);

        return view('driver.dashboard', compact('bus'));
    }

    public function updateStatus(Request $request)
    {
        $busId = Session::get('bus_id');
        $bus = BusSchedule::findOrFail($busId);

        $bus->update(['status' => $request->status]);

        return back()->with('success', 'Status updated to: ' . ucfirst($request->status));
    }

    /** FR-43: GPS pings from driver phone (lat/lng + heading + speed). */
    public function updateLocation(Request $request)
    {
        $busId = Session::get('bus_id');
        if (!$busId) {
            return response()->json(['success' => false, 'error' => 'Not on shift'], 401);
        }

        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'speed' => ['nullable', 'numeric', 'min:0', 'max:80'], // m/s from Geolocation API
            'speed_kmh' => ['nullable', 'numeric', 'min:0', 'max:200'],
        ]);

        $bus = BusSchedule::findOrFail($busId);

        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];

        $heading = isset($data['heading']) ? (float) $data['heading'] : null;
        if ($heading === null && $bus->current_lat !== null && $bus->current_lng !== null) {
            $moved = abs($bus->current_lat - $lat) > 0.00001 || abs($bus->current_lng - $lng) > 0.00001;
            if ($moved) {
                $heading = $this->bearing((float) $bus->current_lat, (float) $bus->current_lng, $lat, $lng);
            } elseif ($bus->heading !== null && $bus->heading !== '') {
                $heading = (float) $bus->heading;
            }
        }

        $speedKmh = null;
        if (isset($data['speed_kmh'])) {
            $speedKmh = (float) $data['speed_kmh'];
        } elseif (isset($data['speed'])) {
            $speedKmh = (float) $data['speed'] * 3.6; // m/s → km/h
        } elseif ($bus->current_lat !== null && $bus->current_lng !== null && $bus->location_updated_at) {
            $dt = max(1, $bus->location_updated_at->diffInSeconds(now()));
            $distKm = $this->haversineKm((float) $bus->current_lat, (float) $bus->current_lng, $lat, $lng);
            if ($distKm > 0.002) { // ignore GPS noise under ~2m
                $speedKmh = min(120, ($distKm / $dt) * 3600);
            } elseif ($bus->speed_kmh !== null) {
                $speedKmh = (float) $bus->speed_kmh * 0.85; // decay when nearly stopped
            }
        }

        $savedHeading = $heading !== null
            ? round($heading, 1)
            : ($bus->heading !== null && $bus->heading !== '' ? (float) $bus->heading : null);
        $savedSpeed = $speedKmh !== null
            ? round($speedKmh, 1)
            : ($bus->speed_kmh !== null ? (float) $bus->speed_kmh : null);

        $bus->update([
            'current_lat' => $lat,
            'current_lng' => $lng,
            'heading' => $savedHeading,
            'speed_kmh' => $savedSpeed,
            'location_updated_at' => now(),
        ]);

        BusLiveStream::publishGps((int) $busId, [
            'lat' => $lat,
            'lng' => $lng,
            'heading' => $savedHeading,
            'speed_kmh' => $savedSpeed,
            'location_updated_at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'lat' => $lat,
            'lng' => $lng,
            'heading' => $savedHeading,
            'speed_kmh' => $savedSpeed,
            'accuracy' => isset($data['accuracy']) ? (float) $data['accuracy'] : null,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /** Initial bearing from point A → B in degrees (0–360, clockwise from north). */
    protected function bearing(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $φ1 = deg2rad($lat1);
        $φ2 = deg2rad($lat2);
        $Δλ = deg2rad($lng2 - $lng1);
        $y = sin($Δλ) * cos($φ2);
        $x = cos($φ1) * sin($φ2) - sin($φ1) * cos($φ2) * cos($Δλ);

        return fmod(rad2deg(atan2($y, $x)) + 360.0, 360.0);
    }

    protected function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $R * asin(min(1, sqrt($a)));
    }
}

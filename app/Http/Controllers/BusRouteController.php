<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BusSchedule;
use Illuminate\Http\Request;

class BusRouteController extends Controller
{
    // Backend Logic for FR-9 & FR-16
    public function index()
    {
        // In a real app, this fetches from DB. For now, we return the view.
        return view('next-bus-arrival');
    }

    // Backend Logic for FR-13 (Route Suggestion)
    public function suggest(Request $request)
    {
        // This accepts the form data from the frontend
        $destination = $request->input('destination');
        
        // Return the view with the data
        return view('route-suggestion', compact('destination'));
    }

    // Backend Logic for FR-17 (Save Favorite)
    public function saveFavorite(Request $request)
    {
        // Logic to save the route to the database would go here
        return response()->json(['status' => 'success', 'message' => 'Route Saved']);
    }

    // 🚀 FR-11: DRIVER SIDE - Updates the location (by bus_id)
    public function updateLocation(Request $request)
    {
        $bus = BusSchedule::where('id', $request->bus_id)->first();
        
        if ($bus) {
            $bus->update([
                'current_lat' => $request->lat,
                'current_lng' => $request->lng
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

        // 1. Calculate expected arrival time based on schedule
        $departureTime = \Carbon\Carbon::parse($bus->departure_time);
        $now = \Carbon\Carbon::now();
        
        // Expected arrival time (assuming 30 minutes travel time from departure)
        $expectedArrivalTime = $departureTime->copy()->addMinutes(30);
        
        // 2. Calculate current ETA based on distance and speed
        // Simulate distance calculation (in real app, use actual GPS distance)
        $distanceKm = 5.0; // Simulated distance remaining
        $normalSpeedKmh = 30.0;  // Normal speed in km/h
        $currentSpeedKmh = $normalSpeedKmh; // Current speed (could be reduced due to traffic)
        
        // Check if bus status indicates delay (traffic, accident, etc.)
        if ($bus->status === 'delayed') {
            $currentSpeedKmh = max(10.0, $normalSpeedKmh * 0.5); // Reduce speed by 50% when delayed
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
        
        // 5. Update bus status in database if delay detected
        if ($isDelayed && $bus->status !== 'delayed') {
            $bus->update([
                'status' => 'delayed',
                'delay_minutes' => round($delayMinutes)
            ]);
        } elseif (!$isDelayed && $bus->status === 'delayed') {
            $bus->update([
                'status' => 'on_time',
                'delay_minutes' => 0
            ]);
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
            'lat' => (float)$bus->current_lat,
            'lng' => (float)$bus->current_lng,
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
}


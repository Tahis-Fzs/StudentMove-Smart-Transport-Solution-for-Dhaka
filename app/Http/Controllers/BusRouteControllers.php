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

        // 1. Get the Schedule (Usual Time)
        // Let's assume the bus is "5km away", averages 30km/h
        $distanceKm = 5.0; // Simulated distance
        $speedKmh = 30.0;  // Average speed in km/h
        $usualMinutes = ($distanceKm / $speedKmh) * 60; // 10 mins

        // 2. Check Delay (Traffic)
        $isDelayed = $bus->status === 'delayed';
        $delayMinutes = $isDelayed ? 15 : 0; // If delayed, add 15 mins

        // 3. Calculate Real Arrival Time
        $totalMinutes = $usualMinutes + $delayMinutes;
        
        return response()->json([
            'lat' => (float)$bus->current_lat,
            'lng' => (float)$bus->current_lng,
            'is_delayed' => $isDelayed,
            'delay_minutes' => $delayMinutes,
            'usual_eta' => round($usualMinutes) . " mins",
            'new_eta' => round($totalMinutes) . " mins",
            'eta_text' => round($totalMinutes) . " mins",
            'status_msg' => $isDelayed ? "Delayed by {$delayMinutes} min (Traffic)" : "On Time"
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
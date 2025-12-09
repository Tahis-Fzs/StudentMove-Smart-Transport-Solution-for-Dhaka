<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusSchedule;
use Illuminate\Support\Facades\Session;

class DriverController extends Controller
{
    // Show the "App" Dashboard
    public function dashboard()
    {
        // Get the bus the driver selected during login
        $busId = Session::get('bus_id');
        if (!$busId) return redirect()->route('driver.login');

        $bus = BusSchedule::findOrFail($busId);
        return view('driver.dashboard', compact('bus'));
    }

    // 🚀 FR-44: Update Bus Status (Traffic, Stopped, etc.)
    public function updateStatus(Request $request)
    {
        $busId = Session::get('bus_id');
        $bus = BusSchedule::findOrFail($busId);

        $bus->update(['status' => $request->status]);

        return back()->with('success', 'Status updated to: ' . ucfirst($request->status));
    }

    // 🚀 FR-43: Receive GPS Pings (AJAX)
    public function updateLocation(Request $request)
    {
        $busId = Session::get('bus_id');
        $bus = BusSchedule::findOrFail($busId);

        $bus->update([
            'current_lat' => $request->lat,
            'current_lng' => $request->lng,
        ]);

        return response()->json(['success' => true]);
    }
}
<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DriverAuthController extends Controller
{
    // Show Login Form
    public function showLogin()
    {
        // If already logged in, redirect to dashboard
        if (session('driver_logged_in')) {
            return redirect()->route('driver.dashboard');
        }
        
        return view('driver.login');
    }

    // Handle Login Logic
    public function login(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:bus_schedules,id',
            'password' => 'required'
        ]);

        // Simple hardcoded check for the demo
        // In a real app, you'd check a 'drivers' table
        if ($request->password === 'driver123') {
            // Regenerate session ID for security
            $request->session()->regenerate();
            
            $request->session()->put('driver_logged_in', true);
            $request->session()->put('bus_id', $request->bus_id); // Driver selects their bus
            $request->session()->save();
            
            return redirect()->route('driver.dashboard');
        }

        return back()->with('error', 'Invalid Driver Password');
    }

    // Logout
    public function logout(Request $request)
    {
        // Clear all session data
        $request->session()->forget('driver_logged_in');
        $request->session()->forget('bus_id');
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Redirect with no-cache headers
        return redirect()->route('driver.login')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
    }
}
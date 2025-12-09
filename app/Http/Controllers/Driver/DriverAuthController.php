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
        return view('driver.login');
    }

    // Handle Login Logic
    public function login(Request $request)
    {
        // Simple hardcoded check for the demo
        // In a real app, you'd check a 'drivers' table
        if ($request->password === 'driver123') {
            Session::put('driver_logged_in', true);
            Session::put('bus_id', $request->bus_id); // Driver selects their bus
            return redirect()->route('driver.dashboard');
        }

        return back()->with('error', 'Invalid Driver Password');
    }

    // Logout
    public function logout()
    {
        Session::forget('driver_logged_in');
        Session::forget('bus_id');
        return redirect()->route('driver.login');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    /**
     * Show admin login form
     */
    public function showLoginForm(): View
    {
        // If already logged in, redirect to dashboard
        if (session('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        
        // Debug: Check if session is working
        Log::info('Admin login form shown', [
            'session_id' => session()->getId(),
            'admin_logged_in' => session('admin_logged_in'),
            'all_session' => session()->all()
        ]);
        
        return view('admin.auth.login');
    }

    /**
     * Handle admin login - Universal password only
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required'],
        ]);

        // Get admin password from .env - fail securely if not set
        $adminPassword = env('ADMIN_PASSWORD');
        
        Log::info('Admin login attempt', [
            'provided_password' => $request->password,
            'expected_password' => $adminPassword,
            'match' => $request->password === $adminPassword,
            'session_id' => session()->getId()
        ]);
        
        if (empty($adminPassword)) {
            Log::error('ADMIN_PASSWORD environment variable is not set. Admin authentication is disabled.');
            return back()->withErrors([
                'password' => 'Admin authentication is not configured. Please contact the system administrator.',
            ]);
        }

        if ($request->password === $adminPassword) {
            // Regenerate session ID for security
            $request->session()->regenerate();
            
            // Set admin logged in flag
            $request->session()->put('admin_logged_in', true);
            
            // Force save session
            $request->session()->save();
            
            Log::info('Admin login successful', [
                'session_id' => $request->session()->getId(),
                'admin_logged_in' => $request->session()->get('admin_logged_in'),
                'session_data' => $request->session()->all()
            ]);
            
            // Direct redirect to dashboard
            return redirect()->route('admin.dashboard')->with('success', 'Login successful!');
        }

        Log::warning('Admin login failed - password mismatch');
        return back()->withErrors([
            'password' => 'Invalid admin password.',
        ]);
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request): RedirectResponse
    {
        // Clear all session data
        $request->session()->forget('admin_logged_in');
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        Log::info('Admin logout successful', [
            'session_id' => $request->session()->getId()
        ]);
        
        // Redirect with no-cache headers
        return redirect()->route('admin.login')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
    }
}


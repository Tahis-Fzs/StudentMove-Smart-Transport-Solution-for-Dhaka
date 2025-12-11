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
        
        if (empty($adminPassword)) {
            Log::error('ADMIN_PASSWORD environment variable is not set. Admin authentication is disabled.');
            return back()->withErrors([
                'password' => 'Admin authentication is not configured. Please contact the system administrator.',
            ]);
        }

        if ($request->password === $adminPassword) {
            Session::put('admin_logged_in', true);
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'password' => 'Invalid admin password.',
        ]);
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request): RedirectResponse
    {
        Session::forget('admin_logged_in');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}


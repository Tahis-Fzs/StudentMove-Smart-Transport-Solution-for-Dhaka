<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DriverAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $driverLoggedIn = $request->session()->get('driver_logged_in');
        
        if (!$driverLoggedIn) {
            // Redirect with no-cache headers to prevent back button access
            return redirect()->route('driver.login')
                ->with('error', 'Please login to access driver dashboard.')
                ->withHeaders([
                    'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                    'Pragma' => 'no-cache',
                    'Expires' => '0'
                ]);
        }

        // Add no-cache headers to driver pages to prevent browser caching
        $response = $next($request);
        
        return $response->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
}

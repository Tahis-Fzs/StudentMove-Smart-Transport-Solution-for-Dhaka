<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->needsProfileCompletion()) {
            return $next($request);
        }

        if ($request->routeIs('profile.*', 'verification.*', 'logout')) {
            return $next($request);
        }

        return redirect()
            ->route('profile.edit', ['complete' => 1])
            ->with('info', 'Add your student ID, phone, and university to finish setting up your account.');
    }
}

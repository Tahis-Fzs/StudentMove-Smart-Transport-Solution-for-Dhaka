<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (! auth()->check()) {
            return view('home');
        }

        $user = auth()->user();

        if ($user->needsProfileCompletion()) {
            return redirect()->route('profile.edit', ['complete' => 1]);
        }

        return redirect()->route('dashboard');
    }
}

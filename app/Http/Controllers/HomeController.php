<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        return auth()->check()
            ? redirect()->route('dashboard')
            : view('home');
    }
}

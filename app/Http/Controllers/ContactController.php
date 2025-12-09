<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Display contact messages page
     */
    public function index(): View
    {
        // In a real app, you would fetch messages from database
        // For now, return a simple view
        return view('contact.index');
    }

    /**
     * Store contact message
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // In a real app, you would save to database and send email
        // For now, just redirect with success message
        
        return redirect()->route('messages')->with('success', 'Your message has been sent successfully!');
    }
}


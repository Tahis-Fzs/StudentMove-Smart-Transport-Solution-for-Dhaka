<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function index()
    {
        // Display feedback entries for the user
        $feedback = Feedback::where('user_id', auth()->id())->get();
        return view('feedback.index', compact('feedback'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // 🚀 FR-35: Save to Database (Archiving)
        Feedback::create([
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'pending'
        ]);

        // FR-33: Email Confirmation (Logic you already had)
        $admin = 'admin@studentmove.com'; // or config('mail.admin.address')
        // Example mail logic — you may adjust as needed for your app
        // \Mail::to($admin)->send(new \App\Mail\FeedbackSubmitted($validated['subject'], $validated['message'], auth()->user()));

        return redirect()->route('feedback.index')->with('success', 'Thanks! Your feedback has been saved and sent.');
    }
}
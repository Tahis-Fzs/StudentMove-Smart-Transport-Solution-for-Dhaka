<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /** Display feedback form (user's feedback) */
    public function index(): View
    {
        $feedbacks = Auth::user()->feedbacks()->latest()->get();
        return view('feedback.index', compact('feedbacks'));
    }

    /** Store feedback */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        Feedback::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'rating' => $request->rating ?? 5,
            'status' => Feedback::STATUS_PENDING,
        ]);

        return redirect()->route('feedback.index')->with('success', 'Feedback submitted successfully!');
    }
}

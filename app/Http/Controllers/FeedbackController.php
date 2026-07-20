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
            'status' => 'pending',
        ]);

        return redirect()->route('feedback.index')->with('success', 'Feedback submitted successfully!');
    }

    /** Admin view all feedback */
    public function adminIndex(): View
    {
        $feedbacks = Feedback::with('user')->orderBy('created_at', 'desc')->get();
        return view('feedback.admin_list', compact('feedbacks'));
    }

    /** Admin reply to feedback */
    public function reply(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'admin_response' => 'required|string|max:1000',
        ]);

        $feedback = Feedback::findOrFail($id);
        $feedback->update([
            'admin_response' => $request->admin_response,
            'status' => 'replied',
        ]);

        return back()->with('success', 'Reply sent successfully!');
    }
}

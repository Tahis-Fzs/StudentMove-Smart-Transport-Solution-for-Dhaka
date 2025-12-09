<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Display feedback form (user's feedback)
     */
    public function index(): View
    {
        $feedbacks = Auth::user()->feedbacks()->latest()->get();
        return view('feedback.index', compact('feedbacks'));
    }

    /**
     * Store feedback
     */
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

    /**
     * 🚀 FR-34: Admin View All Feedback
     */
    public function adminIndex(): View
    {
        // Fetch all feedback, newest first, and eager-load the user relation
        $feedbacks = Feedback::with('user')->orderBy('created_at', 'desc')->get();
        return view('feedback.admin_list', compact('feedbacks'));
    }

    /**
     * 🚀 FR-34: Admin Reply Logic
     */
    public function reply(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'admin_response' => 'required|string|max:1000',
        ]);

        $feedback = Feedback::findOrFail($id);
        
        // Update Database with the reply
        $feedback->update([
            'admin_response' => $request->admin_response,
            'status' => 'replied',
            // 'is_archived' => true // Optional: Auto-archive after reply
        ]);

        // Optional: Send Email to User (implement Mailable as needed)
        // \Mail::to($feedback->user->email)->send(new FeedbackReplyEmail($request->admin_response));

        return back()->with('success', 'Reply sent successfully!');
    }
}
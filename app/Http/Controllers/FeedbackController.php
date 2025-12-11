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
        // #region agent log
        @file_put_contents(
            base_path('.cursor/debug.log'),
            json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'feedback',
                'hypothesisId' => 'H1',
                'location' => 'FeedbackController@store',
                'message' => 'entry',
                'data' => [
                    'user_id' => Auth::id(),
                    'subject_len' => strlen((string) $request->subject),
                    'message_len' => strlen((string) $request->message),
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
        // #endregion

        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $fb = Feedback::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'rating' => $request->rating ?? 5,
            'status' => 'pending',
        ]);

        // #region agent log
        @file_put_contents(
            base_path('.cursor/debug.log'),
            json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'feedback',
                'hypothesisId' => 'H2',
                'location' => 'FeedbackController@store',
                'message' => 'created',
                'data' => [
                    'id' => $fb->id,
                    'user_id' => $fb->user_id,
                    'status' => $fb->status,
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
        // #endregion

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

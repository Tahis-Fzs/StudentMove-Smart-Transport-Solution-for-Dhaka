<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\InboxMessage;
use App\Services\InboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function __construct(protected InboxService $inbox)
    {
    }

    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'open');

        $query = Feedback::query()->with('user')->latest();

        $query = match ($filter) {
            'pending' => $query->where('status', Feedback::STATUS_PENDING),
            'replied' => $query->where('status', Feedback::STATUS_REPLIED),
            'archived' => $query->where('status', Feedback::STATUS_ARCHIVED),
            default => $query->whereIn('status', [Feedback::STATUS_PENDING, Feedback::STATUS_REPLIED]),
        };

        $feedbacks = $query->paginate(15)->withQueryString();

        $counts = [
            'open' => Feedback::whereIn('status', [Feedback::STATUS_PENDING, Feedback::STATUS_REPLIED])->count(),
            'pending' => Feedback::where('status', Feedback::STATUS_PENDING)->count(),
            'replied' => Feedback::where('status', Feedback::STATUS_REPLIED)->count(),
            'archived' => Feedback::where('status', Feedback::STATUS_ARCHIVED)->count(),
        ];

        return view('admin.feedback.index', compact('feedbacks', 'filter', 'counts'));
    }

    public function reply(Request $request, Feedback $feedback): RedirectResponse
    {
        $data = $request->validate([
            'admin_response' => ['required', 'string', 'max:1000'],
        ]);

        $feedback->update([
            'admin_response' => trim($data['admin_response']),
            'status' => Feedback::STATUS_REPLIED,
        ]);

        if ($feedback->user) {
            try {
                $this->inbox->push(
                    $feedback->user,
                    InboxMessage::TYPE_SYSTEM,
                    'Reply to your feedback · ' . $feedback->subject,
                    $feedback->admin_response,
                    ['feedback_id' => $feedback->id],
                    'bi-chat-left-text'
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', 'Reply sent — student notified in their inbox.');
    }

    public function archive(Feedback $feedback): RedirectResponse
    {
        $feedback->update(['status' => Feedback::STATUS_ARCHIVED]);

        return back()->with('success', 'Feedback archived.');
    }

    public function restore(Feedback $feedback): RedirectResponse
    {
        $feedback->update([
            'status' => $feedback->admin_response
                ? Feedback::STATUS_REPLIED
                : Feedback::STATUS_PENDING,
        ]);

        return back()->with('success', 'Feedback restored.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\InboxMessage;
use App\Models\Notification;
use App\Services\InboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserNotificationController extends Controller
{
    public function __construct(protected InboxService $inbox)
    {
    }

    /** Personal inbox + network announcements. */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'inbox');
        if (!in_array($tab, ['inbox', 'announcements'], true)) {
            $tab = 'inbox';
        }

        $inbox = $user->inboxMessages()->limit(50)->get();
        $unreadCount = $user->inboxMessages()->whereNull('read_at')->count();
        $announcements = Notification::visibleFor($user);

        // Soft welcome message once per account
        if ($inbox->isEmpty() && !cache()->has('inbox_welcome_' . $user->id)) {
            $this->inbox->push(
                $user,
                InboxMessage::TYPE_SYSTEM,
                'Welcome to your inbox',
                'Delay alerts, booking confirmations, and arrival updates will show up here — not only as toasts.',
                ['welcome' => true]
            );
            cache()->forever('inbox_welcome_' . $user->id, true);
            $inbox = $user->inboxMessages()->limit(50)->get();
            $unreadCount = $user->inboxMessages()->whereNull('read_at')->count();
        }

        return view('notifications', compact('inbox', 'announcements', 'unreadCount', 'tab'));
    }

    public function markRead(InboxMessage $inboxMessage): RedirectResponse
    {
        abort_unless($inboxMessage->user_id === Auth::id(), 403);
        $inboxMessage->markRead();

        return back()->with('success', 'Marked as read.');
    }

    public function markAllRead(): RedirectResponse
    {
        Auth::user()->inboxMessages()->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'All messages marked as read.');
    }

    public function destroy(InboxMessage $inboxMessage): RedirectResponse
    {
        abort_unless($inboxMessage->user_id === Auth::id(), 403);
        $inboxMessage->delete();

        return back()->with('success', 'Message removed.');
    }

    public function settings()
    {
        $user = Auth::user();

        return view('notification_settings', compact('user'));
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $user->update([
            'bus_delay_notifications' => $request->has('bus_delay_notifications'),
            'route_change_alerts' => $request->has('route_change_alerts'),
            'promotional_offers' => $request->has('promotional_offers'),
        ]);

        return back()->with('success', 'Preferences saved successfully!');
    }
}

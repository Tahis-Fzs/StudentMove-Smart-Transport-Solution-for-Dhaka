<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * User notification settings page
     */
    public function settings()
    {
        $user = Auth::user();
        return view('notification_settings', compact('user'));
    }

    /**
     * Display all notifications
     */
    public function index(): View
    {
        $notifications = Notification::latest()->paginate(15);
        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        return view('admin.notifications.create');
    }

    /**
     * Store new notification
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'icon_color' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'in:info,success,warning,error'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        Notification::create([
            'message' => $request->message,
            'icon' => $request->icon ?? 'bi-bell',
            'icon_color' => $request->icon_color ?? 'blue',
            'type' => $request->type ?? 'info',
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.notifications.index')->with('success', 'Notification created successfully!');
    }

    /**
     * Show edit form
     */
    public function edit(Notification $notification): View
    {
        return view('admin.notifications.edit', compact('notification'));
    }

    /**
     * Update notification
     */
    public function update(Request $request, Notification $notification): RedirectResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'icon_color' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'in:info,success,warning,error'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $notification->update([
            'message' => $request->message,
            'icon' => $request->icon ?? 'bi-bell',
            'icon_color' => $request->icon_color ?? 'blue',
            'type' => $request->type ?? 'info',
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.notifications.index')->with('success', 'Notification updated successfully!');
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification): RedirectResponse
    {
        $notification->delete();
        return redirect()->route('admin.notifications.index')->with('success', 'Notification deleted successfully!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\SystemAlert;

class UserNotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Auth::user()->notifications;
        return view('notifications', compact('notifications'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $message = $request->input('message', 'This is a test notification.');
        $user->notify(new SystemAlert($message, 'system'));
        return back()->with('success', 'Notification sent to Email & App!');
    }

    // 🚀 FR-28: Show Settings Page
    public function settings()
    {
        $user = Auth::user();
        return view('notification_settings', compact('user'));
    }

    // 🚀 FR-28: Update Preferences Logic
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        
        // Update the checkboxes in the database
        $user->update([
            'bus_delay_notifications'   => $request->has('bus_delay_notifications'),
            'route_change_alerts'       => $request->has('route_change_alerts'),
            'promotional_offers'        => $request->has('promotional_offers'),
        ]);

        return back()->with('success', 'Preferences saved successfully!');
    }
}
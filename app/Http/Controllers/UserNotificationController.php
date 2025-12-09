<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\SystemAlert; // 👈 IMPORT THIS

class UserNotificationController extends Controller
{
    /**
     * Display all active notifications for users
     */
    public function index(): View
    {
        // You can use either logic based on requirements:
        // $notifications = Notification::active()
        //     ->orderBy('sort_order')
        //     ->orderBy('created_at', 'desc')
        //     ->get();

        // OR use Laravel's built-in relationship:
        $notifications = Auth::user()->notifications; // Uses Laravel's built-in relationship

        return view('notifications', compact('notifications'));
    }

    /**
     * Store a notification and send via Email & Push (FR-26 & FR-27)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $message = $request->input('message', 'This is a test notification.');

        // This single line sends the Email AND saves to Database
        $user->notify(new SystemAlert($message, 'system'));

        return back()->with('success', 'Notification sent to Email & App!');
    }
}
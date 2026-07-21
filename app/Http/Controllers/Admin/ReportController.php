<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use Carbon\Carbon;

class ReportController extends Controller
{
    // FR-39: Generate Reports
    public function index()
    {
        // 1. Calculate Income (From Subscriptions)
        $totalIncome = Subscription::where('status', 'completed')->sum('amount');
        $monthlyIncome = Subscription::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->sum('amount');

        // 2. Calculate User Growth
        $newUsers = User::where('created_at', '>=', Carbon::now()->subWeek())->count();

        // 3. Subscription Stats
        $activeSubs = Subscription::currentlyActive()->count();

        return view('admin.reports.index', compact('totalIncome', 'monthlyIncome', 'newUsers', 'activeSubs'));
    }
}
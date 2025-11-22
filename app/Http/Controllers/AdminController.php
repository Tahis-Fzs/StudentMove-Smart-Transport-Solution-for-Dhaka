<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Offer;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function dashboard(): View
    {
        $totalUsers = User::count();
        $totalOffers = Offer::count();
        $activeOffers = Offer::active()->count();
        $totalNotifications = Notification::count();
        $activeNotifications = Notification::active()->count();
        $recentUsers = User::latest()->take(5)->get();
        
        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalOffers' => $totalOffers,
            'activeOffers' => $activeOffers,
            'totalNotifications' => $totalNotifications,
            'activeNotifications' => $activeNotifications,
            'recentUsers' => $recentUsers,
        ]);
    }

    /**
     * Display all users
     */
    public function users(): View
    {
        $users = User::latest()->paginate(15);
        return view('admin.users', compact('users'));
    }

    /**
     * Show user details
     */
    public function show(User $user): View
    {
        return view('admin.user-show', compact('user'));
    }

    /**
     * Show edit user form
     */
    public function edit(User $user): View
    {
        return view('admin.user-edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'university' => ['nullable', 'string', 'max:255'],
            'student_id' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'department' => ['nullable', 'string', 'max:255'],
            'year_of_study' => ['nullable', 'string', 'max:10'],
            'current_address' => ['nullable', 'string', 'max:500'],
            'home_address' => ['nullable', 'string', 'max:500'],
            'preferred_language' => ['nullable', 'string', 'max:10'],
        ]);

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->name = $request->first_name . ' ' . $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->university = $request->university;
        $user->student_id = $request->student_id;
        $user->date_of_birth = $request->date_of_birth;
        $user->department = $request->department;
        $user->year_of_study = $request->year_of_study;
        $user->current_address = $request->current_address;
        $user->home_address = $request->home_address;
        $user->preferred_language = $request->preferred_language;

        $user->save();

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    /**
     * Delete user
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully!');
    }

    /**
     * Search users
     */
    public function search(Request $request): View
    {
        $query = $request->get('q');
        
        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('university', 'like', "%{$query}%")
            ->orWhere('student_id', 'like', "%{$query}%")
            ->latest()
            ->paginate(15);

        return view('admin.users', compact('users', 'query'));
    }
}



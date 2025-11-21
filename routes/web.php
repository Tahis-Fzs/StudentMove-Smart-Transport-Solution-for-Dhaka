<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\NotificationController;
use App\Models\Offer;
use App\Models\Notification;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/subscription', function () {
    return view('subscription');
})->name('subscription');

Route::get('/notifications', function () {
    $notifications = Notification::active()->orderBy('sort_order')->orderBy('created_at', 'desc')->get();
    return view('notifications', compact('notifications'));
})->name('notifications');

Route::get('/messages', [App\Http\Controllers\ContactController::class, 'index'])->name('messages');
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'store'])->name('contact.store');

Route::get('/offers', function () {
    $offers = Offer::active()->orderBy('sort_order')->orderBy('created_at', 'desc')->get();
    return view('offers', compact('offers'));
})->name('offers');

Route::get('/next-bus-arrival', function () {
    return view('next-bus-arrival');
})->name('next-bus-arrival');

Route::get('/route-suggestion', function () {
    return view('route-suggestion');
})->name('route-suggestion');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/feedback', [App\Http\Controllers\FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback', [App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store');
});

// Admin Panel Routes (Separate Authentication)
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Login Routes (Public)
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Admin Protected Routes
    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // User Management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/search', [AdminController::class, 'search'])->name('users.search');
        Route::get('/users/{user}', [AdminController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
        
        // Offers Management
        Route::resource('offers', OfferController::class);
        
        // Notifications Management
        Route::resource('notifications', NotificationController::class);
    });
});

require __DIR__.'/auth.php';
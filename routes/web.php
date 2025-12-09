<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\NotificationController; //Nahid
use App\Http\Controllers\Admin\BusController; // added for admin bus management
use App\Http\Controllers\Admin\ReportController; // added for admin report management
use App\Http\Controllers\BusRouteController; // Tahsin
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\UserNotificationController; // <-- Add this line!
use App\Models\Offer;
use App\Models\Notification; //Nahid
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Use controller-based subscription routes (removed duplicate closure-based route)
Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription');
Route::post('/subscription', [SubscriptionController::class, 'store'])->middleware('auth')->name('subscription.store');
Route::get('/subscription/history', [SubscriptionController::class, 'history'])->middleware('auth')->name('subscription.history');
Route::get('/subscription/invoice/{invoice}/download', [SubscriptionController::class, 'downloadInvoice'])->middleware('auth')->name('subscription.invoice.download');

Route::get('/notifications', function () {
    $notifications = Notification::active()->orderBy('sort_order')->orderBy('created_at', 'desc')->get();
    return view('notifications', compact('notifications'));
})->name('notifications');

Route::get('/messages', [ContactController::class, 'index'])->name('messages');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/offers', function () {
    $offers = Offer::active()->orderBy('sort_order')->orderBy('created_at', 'desc')->get();
    return view('offers', compact('offers'));
})->name('offers');

// FR-28: Notification Settings
Route::get('/notifications/settings', [UserNotificationController::class, 'settings'])->name('notifications.settings');
Route::post('/notifications/settings', [UserNotificationController::class, 'updateSettings'])->name('notifications.update');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

    // Tahsin — BusRouteController-based routes (require auth)
    Route::get('/next-bus-arrival', [BusRouteController::class, 'index'])->name('next-bus-arrival');
    Route::get('/route-suggestion', [BusRouteController::class, 'suggest'])->name('route-suggestion');
    Route::post('/save-route', [BusRouteController::class, 'saveFavorite'])->name('route.save');

    // FR-11 & FR-12: Dynamic GPS APIs
    Route::post('/api/bus/update-location', [BusRouteController::class, 'updateLocation'])->name('api.bus.update');
    Route::get('/api/bus/get-location/{id}', [BusRouteController::class, 'getBusLocation'])->name('api.bus.get');
});

// FR-42: Driver Auth (public driver routes)
Route::get('/driver/login', [App\Http\Controllers\Driver\DriverAuthController::class, 'showLogin'])->name('driver.login');
Route::post('/driver/login', [App\Http\Controllers\Driver\DriverAuthController::class, 'login'])->name('driver.login.post');
Route::post('/driver/logout', [App\Http\Controllers\Driver\DriverAuthController::class, 'logout'])->name('driver.logout');

// FR-43 & FR-44: Driver Dashboard & Logic
Route::get('/driver/dashboard', [App\Http\Controllers\Driver\DriverController::class, 'dashboard'])->name('driver.dashboard');
Route::post('/driver/status', [App\Http\Controllers\Driver\DriverController::class, 'updateStatus'])->name('driver.status');
Route::post('/driver/gps', [App\Http\Controllers\Driver\DriverController::class, 'updateLocation'])->name('driver.gps');

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

        // FR-40: User Suspension & Sub Control
        Route::post('users/{id}/ban', [AdminController::class, 'toggleBan'])->name('admin.users.ban');
        Route::post('users/{id}/cancel-sub', [AdminController::class, 'cancelSubscription'])->name('admin.users.cancel_sub');
        
        // Offers Management
        Route::resource('offers', OfferController::class);

        // Notifications Management (exclude 'show' because NotificationController does not implement show())
        Route::resource('notifications', NotificationController::class)->except(['show']);

        // Bus management routes: index, create, store, destroy
        Route::resource('buses', BusController::class)->only(['index', 'create', 'store', 'destroy']);

        // Manual GPS edit/override routes for buses (FR-38)
        Route::get('buses/{id}/gps', [BusController::class, 'editGps'])->name('buses.gps');
        Route::post('buses/{id}/gps', [BusController::class, 'updateGps'])->name('buses.gps.update');

        // FR-39: Admin Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

        // FR-41: Activity Logs (renamed to logs, avoid reserved log filename)
        Route::get('logs', [AdminController::class, 'logs'])->name('logs');
    });
});

require __DIR__.'/auth.php';
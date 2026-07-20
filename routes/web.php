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
use App\Http\Controllers\AiController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\UserNotificationController; // <-- Add this line!
use App\Models\Offer;
use App\Models\Notification; //Nahid
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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

Route::get('/offers', function () {
    $offers = Offer::active()->orderBy('sort_order')->orderByDesc('discount_percentage')->get();

    return view('offers', compact('offers'));
})->middleware(['auth', 'verified'])->name('offers');

// SSLCommerz callbacks (CSRF-exempt — gateway POSTs here)
Route::match(['get', 'post'], '/subscription/sslcommerz/success', [SubscriptionController::class, 'sslSuccess'])->name('subscription.ssl.success');
Route::match(['get', 'post'], '/subscription/sslcommerz/fail', [SubscriptionController::class, 'sslFail'])->name('subscription.ssl.fail');
Route::match(['get', 'post'], '/subscription/sslcommerz/cancel', [SubscriptionController::class, 'sslCancel'])->name('subscription.ssl.cancel');
Route::post('/subscription/sslcommerz/ipn', [SubscriptionController::class, 'sslIpn'])->name('subscription.ssl.ipn');

Route::middleware('auth')->group(function () {
    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/inbox/{inboxMessage}/read', [UserNotificationController::class, 'markRead'])->name('notifications.inbox.read');
    Route::post('/notifications/inbox/read-all', [UserNotificationController::class, 'markAllRead'])->name('notifications.inbox.readAll');
    Route::delete('/notifications/inbox/{inboxMessage}', [UserNotificationController::class, 'destroy'])->name('notifications.inbox.destroy');
    Route::get('/notifications/settings', [UserNotificationController::class, 'settings'])->name('notifications.settings');
    Route::post('/notifications/settings', [UserNotificationController::class, 'updateSettings'])->name('notifications.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/ai/generate', [AiController::class, 'generate'])
        ->middleware('throttle:30,1')
        ->name('ai.generate');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [ChatController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('chat.store');
    Route::get('/chat/poll', [ChatController::class, 'poll'])->name('chat.poll');
    Route::post('/chat/clear', [ChatController::class, 'clear'])->name('chat.clear');

    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

    Route::get('/bookings', [\App\Http\Controllers\BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store'])->name('bookings.store');
    Route::post('/bookings/{booking}/cancel', [\App\Http\Controllers\BookingController::class, 'cancel'])->name('bookings.cancel');
});

// Public bus routes and APIs (no auth required for map)
Route::get('/next-bus-arrival', [BusRouteController::class, 'index'])->name('next-bus-arrival');
Route::get('/route-suggestion', [BusRouteController::class, 'suggest'])->name('route-suggestion');
Route::post('/api/bus/update-location', [BusRouteController::class, 'updateLocation'])->name('api.bus.update');
Route::get('/api/bus/get-location/{id}', [BusRouteController::class, 'getBusLocation'])->name('api.bus.get');
Route::get('/api/bus/stream/{id}', [\App\Http\Controllers\Api\BusStreamController::class, 'stream'])->name('api.bus.stream');

Route::middleware('auth')->group(function () {
    Route::post('/save-route', [BusRouteController::class, 'saveFavorite'])->name('route.save');
    Route::delete('/saved-routes/{savedRoute}', [BusRouteController::class, 'destroyFavorite'])->name('route.favorite.destroy');
});

// FR-42: Driver Auth (public driver routes)
Route::get('/driver/login', [App\Http\Controllers\Driver\DriverAuthController::class, 'showLogin'])->name('driver.login');
Route::post('/driver/login', [App\Http\Controllers\Driver\DriverAuthController::class, 'login'])->name('driver.login.post');
Route::post('/driver/logout', [App\Http\Controllers\Driver\DriverAuthController::class, 'logout'])->name('driver.logout');

// FR-43 & FR-44: Driver Dashboard & Logic (Protected)
Route::middleware('driver.auth')->group(function () {
    Route::get('/driver/dashboard', [App\Http\Controllers\Driver\DriverController::class, 'dashboard'])->name('driver.dashboard');
    Route::post('/driver/status', [App\Http\Controllers\Driver\DriverController::class, 'updateStatus'])->name('driver.status');
    Route::post('/driver/gps', [App\Http\Controllers\Driver\DriverController::class, 'updateLocation'])->name('driver.gps');
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

        // FR-40: User Suspension & Sub Control
        Route::post('users/{id}/ban', [AdminController::class, 'toggleBan'])->name('users.ban');
        Route::post('users/{id}/cancel-sub', [AdminController::class, 'cancelSubscription'])->name('users.cancel_sub');
        
        // Offers Management
        Route::resource('offers', OfferController::class);

        // Notifications Management (exclude 'show' because NotificationController does not implement show())
        Route::resource('notifications', NotificationController::class)->except(['show']);

        // Bus management routes: index, create, store, destroy
        Route::resource('buses', BusController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        // Manual GPS edit/override routes for buses (FR-38)
        Route::get('buses/{id}/gps', [BusController::class, 'editGps'])->name('buses.gps');
        Route::post('buses/{id}/gps', [BusController::class, 'updateGps'])->name('buses.gps.update');

        // FR-39: Admin Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

        // FR-41: Activity Logs (renamed to logs, avoid reserved log filename)
        Route::get('logs', [AdminController::class, 'logs'])->name('logs');

        // Support chat (student ↔ admin)
        Route::get('chat', [\App\Http\Controllers\Admin\ChatController::class, 'index'])->name('chat.index');
        Route::get('chat/{thread}', [\App\Http\Controllers\Admin\ChatController::class, 'show'])->name('chat.show');
        Route::post('chat/{thread}/reply', [\App\Http\Controllers\Admin\ChatController::class, 'reply'])->name('chat.reply');
        Route::post('chat/{thread}/close', [\App\Http\Controllers\Admin\ChatController::class, 'close'])->name('chat.close');
        Route::post('chat/{thread}/reopen', [\App\Http\Controllers\Admin\ChatController::class, 'reopen'])->name('chat.reopen');

        // FR-34 / FR-35: Feedback review + archive
        Route::get('feedback', [\App\Http\Controllers\Admin\FeedbackController::class, 'index'])->name('feedback.index');
        Route::post('feedback/{feedback}/reply', [\App\Http\Controllers\Admin\FeedbackController::class, 'reply'])->name('feedback.reply');
        Route::post('feedback/{feedback}/archive', [\App\Http\Controllers\Admin\FeedbackController::class, 'archive'])->name('feedback.archive');
        Route::post('feedback/{feedback}/restore', [\App\Http\Controllers\Admin\FeedbackController::class, 'restore'])->name('feedback.restore');
    });
});

require __DIR__.'/auth.php';

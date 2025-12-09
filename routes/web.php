<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\BusController; // added for admin bus management
use App\Http\Controllers\BusRouteController; // Tahsin
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\FeedbackController;
use App\Models\Offer;
use App\Models\Notification;
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
        
        // Notifications Management (exclude 'show' because NotificationController does not implement show())
        Route::resource('notifications', NotificationController::class)->except(['show']);

        // Bus management routes: index, create, store, destroy
        // These provide:
        // GET    /admin/buses           -> admin.buses.index
        // GET    /admin/buses/create    -> admin.buses.create
        // POST   /admin/buses           -> admin.buses.store
        // DELETE /admin/buses/{bus}     -> admin.buses.destroy
        Route::resource('buses', BusController::class)->only(['index', 'create', 'store', 'destroy']);
    });
});

require __DIR__.'/auth.php';
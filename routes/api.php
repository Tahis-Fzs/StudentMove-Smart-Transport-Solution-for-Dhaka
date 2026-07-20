<?php

use App\Http\Controllers\Api\LiveBusController;
use App\Http\Controllers\Api\MobileAuthController;
use Illuminate\Support\Facades\Route;

/*
| Mobile / Flutter API
| Base: /api/v1
| Auth: POST /api/v1/auth/firebase { "id_token": "..." } → { token }
| Then: Authorization: Bearer {token}
*/

Route::prefix('v1')->group(function () {
    Route::post('/auth/firebase', [MobileAuthController::class, 'firebase'])
        ->middleware('throttle:30,1');

    Route::get('/buses/live', [LiveBusController::class, 'index']);
    Route::get('/buses/{id}', [LiveBusController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [MobileAuthController::class, 'me']);
        Route::post('/buses/location', [LiveBusController::class, 'updateLocation'])
            ->middleware('throttle:120,1');
    });
});

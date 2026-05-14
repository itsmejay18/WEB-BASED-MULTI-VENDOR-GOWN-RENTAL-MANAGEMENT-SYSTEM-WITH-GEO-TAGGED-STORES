<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GeoSearchController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\Provider\LocationController as ProviderLocationController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\Renter\LocationController as RenterLocationController;
use App\Http\Controllers\Api\Renter\MapController as RenterMapController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::get('/search', SearchController::class);
    Route::get('/search/nearby', [GeoSearchController::class, 'nearby']);

    Route::get('/items', [ItemController::class, 'index']);
    Route::get('/items/nearby', [GeoSearchController::class, 'items']);
    Route::get('/items/{item:slug}', [ItemController::class, 'show']);
    Route::get('/items/{item:slug}/availability', [ItemController::class, 'checkAvailability']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show']);

    Route::get('/providers', [ProviderController::class, 'index']);
    Route::get('/providers/nearby', [GeoSearchController::class, 'providers']);
    Route::get('/providers/{provider}', [ProviderController::class, 'show']);
    Route::get('/providers/{provider}/items', [ProviderController::class, 'items']);
    Route::get('/providers/{provider}/reviews', [ProviderController::class, 'reviews']);

    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/{review}', [ReviewController::class, 'show']);

    Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    Route::get('/user/measurements', [UserController::class, 'measurements']);
    Route::put('/user/measurements', [UserController::class, 'updateMeasurements']);
    Route::get('/user/addresses', [UserController::class, 'addresses']);
    Route::post('/user/addresses', [UserController::class, 'addAddress']);
    Route::put('/user/addresses/{addressId}', [UserController::class, 'updateAddress']);
    Route::delete('/user/addresses/{addressId}', [UserController::class, 'deleteAddress']);

    Route::apiResource('bookings', BookingController::class);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
    Route::post('/payments/confirm', [PaymentController::class, 'confirm']);
    Route::get('/payments/history', [PaymentController::class, 'history']);

    Route::apiResource('reviews', ReviewController::class)->except(['index', 'show']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{item}', [WishlistController::class, 'add']);
    Route::delete('/wishlist/{item}', [WishlistController::class, 'remove']);

    Route::get('/conversations', [MessageController::class, 'conversations']);
    Route::get('/conversations/{conversation}', [MessageController::class, 'show']);
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'sendMessage']);

    Route::get('/renter/map', [RenterMapController::class, 'index']);
    Route::get('/renter/locations', [RenterLocationController::class, 'nearby']);

    Route::middleware(['provider'])->group(function () {
        Route::post('/provider/items', [ItemController::class, 'store']);
        Route::put('/provider/items/{item:slug}', [ItemController::class, 'update']);
        Route::delete('/provider/items/{item:slug}', [ItemController::class, 'destroy']);
        Route::get('/provider/locations', [ProviderLocationController::class, 'index']);
        Route::post('/provider/locations/geocode', [ProviderLocationController::class, 'geocode']);
    });

    Route::middleware(['provider', 'provider.verified'])->group(function () {
        Route::get('/provider/dashboard', DashboardController::class);
    });

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/providers', [AdminController::class, 'providers']);
        Route::get('/providers/pending', [AdminController::class, 'pendingProviders']);
        Route::put('/providers/{provider}/approve', [AdminController::class, 'approveProvider']);
        Route::put('/providers/{provider}/reject', [AdminController::class, 'rejectProvider']);
        Route::get('/analytics', [AdminController::class, 'analytics']);
    });
});

<?php

use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Web\Admin\ProviderVerificationController;
use App\Http\Controllers\Web\Admin\TagController as AdminTagController;
use App\Http\Controllers\Web\Admin\UserManagementController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\Provider\BookingController as ProviderBookingController;
use App\Http\Controllers\Web\Provider\ItemController as ProviderItemController;
use App\Http\Controllers\Web\Provider\LocationController as ProviderLocationController;
use App\Http\Controllers\Web\Provider\ProfileController as ProviderProfileController;
use App\Http\Controllers\Web\Provider\VariantController as ProviderVariantController;
use App\Http\Controllers\Web\ProviderDashboardController;
use App\Http\Controllers\Web\Renter\BookingController as RenterBookingController;
use App\Http\Controllers\Web\Renter\ItemController as RenterItemController;
use App\Http\Controllers\Web\Renter\ProfileController as RenterProfileController;
use App\Http\Controllers\Web\Renter\SearchController as RenterSearchController;
use App\Http\Controllers\Web\Renter\WishlistController as RenterWishlistController;
use App\Http\Controllers\Web\RenterDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/browse', [PageController::class, 'browse'])->name('browse');
    Route::get('/item/{item:slug}', [PageController::class, 'item'])->name('item.show');
    Route::get('/providers', [PageController::class, 'providers'])->name('providers');
    Route::get('/api/documentation', [PageController::class, 'apiDocs'])->name('api.docs');

    Route::get('/dashboard', [RenterDashboardController::class, 'index'])
        ->middleware('role:renter')
        ->name('dashboard');

    Route::prefix('user')->name('user.')->middleware('role:renter,provider,admin')->group(function () {
        Route::get('/profile', [RenterProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [RenterProfileController::class, 'update'])->name('profile.update');

        Route::get('/measurements', [RenterProfileController::class, 'measurements'])->name('measurements');
        Route::put('/measurements', [RenterProfileController::class, 'updateMeasurements'])->name('measurements.update');

        Route::get('/addresses', [RenterProfileController::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [RenterProfileController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/addresses/{address}', [RenterProfileController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/addresses/{address}', [RenterProfileController::class, 'destroyAddress'])->name('addresses.destroy');
    });

    Route::prefix('renter')->name('renter.')->middleware('role:renter')->group(function () {
        Route::get('/dashboard', [RenterDashboardController::class, 'index'])->name('dashboard');
        Route::get('/search', [RenterSearchController::class, 'index'])->name('search');
        Route::get('/map', [RenterSearchController::class, 'map'])->name('map');
        Route::get('/items/{item:slug}', [RenterItemController::class, 'show'])->name('items.show');

        Route::get('/wishlist', [RenterWishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist/{item}', [RenterWishlistController::class, 'store'])->name('wishlist.store');
        Route::delete('/wishlist/{item}', [RenterWishlistController::class, 'destroy'])->name('wishlist.destroy');

        Route::get('/bookings', [RenterBookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [RenterBookingController::class, 'show'])->name('bookings.show');
        Route::post('/bookings', [RenterBookingController::class, 'store'])->name('bookings.store');
        Route::post('/bookings/{booking}/cancel', [RenterBookingController::class, 'cancel'])->name('bookings.cancel');
    });

    Route::prefix('provider')->name('provider.')->middleware('role:provider')->group(function () {
        Route::get('/dashboard', [ProviderDashboardController::class, 'index'])->name('dashboard');
        Route::get('/analytics', [ProviderDashboardController::class, 'analytics'])->name('analytics');
        Route::get('/profile', [ProviderProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProviderProfileController::class, 'update'])->name('profile.update');
        Route::get('/business-hours', [ProviderProfileController::class, 'businessHours'])->name('business-hours');
        Route::put('/locations/{location}/hours', [ProviderProfileController::class, 'updateBusinessHours'])->name('hours.update');

        Route::get('/items', [ProviderItemController::class, 'index'])->name('items.index');
        Route::get('/items/create', [ProviderItemController::class, 'create'])->name('items.create');
        Route::post('/items', [ProviderItemController::class, 'store'])->name('items.store');
        Route::get('/items/{item}/edit', [ProviderItemController::class, 'edit'])->name('items.edit');
        Route::put('/items/{item}', [ProviderItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{item}', [ProviderItemController::class, 'destroy'])->name('items.destroy');

        Route::post('/variants', [ProviderVariantController::class, 'store'])->name('variants.store');
        Route::put('/variants/{variant}', [ProviderVariantController::class, 'update'])->name('variants.update');
        Route::delete('/variants/{variant}', [ProviderVariantController::class, 'destroy'])->name('variants.destroy');

        Route::get('/bookings', [ProviderBookingController::class, 'index'])->name('bookings.index');
        Route::post('/bookings/{booking}/approve', [ProviderBookingController::class, 'approve'])->name('bookings.approve');
        Route::post('/bookings/{booking}/reject', [ProviderBookingController::class, 'reject'])->name('bookings.reject');
        Route::post('/bookings/{booking}/ready', [ProviderBookingController::class, 'ready'])->name('bookings.ready');
        Route::post('/bookings/{booking}/picked-up', [ProviderBookingController::class, 'pickedUp'])->name('bookings.picked-up');
        Route::post('/bookings/{booking}/returned', [ProviderBookingController::class, 'returned'])->name('bookings.returned');

        Route::get('/locations', [ProviderLocationController::class, 'index'])->name('locations.index');
        Route::post('/locations', [ProviderLocationController::class, 'store'])->name('locations.store');
        Route::put('/locations/{location}', [ProviderLocationController::class, 'update'])->name('locations.update');
        Route::delete('/locations/{location}', [ProviderLocationController::class, 'destroy'])->name('locations.destroy');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/ban', [UserManagementController::class, 'ban'])->name('users.ban');
        Route::post('/users/{user}/restore', [UserManagementController::class, 'restore'])->name('users.restore');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/bulk-action', [UserManagementController::class, 'bulkAction'])->name('users.bulk-action');

        Route::get('/providers', [ProviderVerificationController::class, 'index'])->name('providers.index');
        Route::get('/providers/pending', [ProviderVerificationController::class, 'index'])
            ->defaults('status', 'pending')
            ->name('providers.pending');
        Route::get('/providers/{provider}', [ProviderVerificationController::class, 'show'])->name('providers.show');
        Route::post('/providers/{provider}/verify', [ProviderVerificationController::class, 'verify'])->name('providers.verify');
        Route::post('/providers/{provider}/reject', [ProviderVerificationController::class, 'reject'])->name('providers.reject');
        Route::post('/providers/{provider}/suspend', [ProviderVerificationController::class, 'suspend'])->name('providers.suspend');
        Route::post('/providers/{provider}/unsuspend', [ProviderVerificationController::class, 'unsuspend'])->name('providers.unsuspend');

        Route::resource('/categories', AdminCategoryController::class)->except(['show']);
        Route::resource('/tags', AdminTagController::class)->only(['index', 'store', 'update', 'destroy']);
    });
});

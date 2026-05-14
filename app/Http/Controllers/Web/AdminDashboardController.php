<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $stats = [
            'total_users' => User::count(),
            'total_renters' => User::where('role', 'renter')->count(),
            'total_providers' => Provider::count(),
            'pending_provider_reviews' => Provider::where('verification_status', 'pending')->count(),
            'total_items' => Item::count(),
            'active_items' => Item::where('is_active', true)->count(),
            'total_bookings' => Booking::count(),
            'total_revenue' => (float) Booking::where('payment_status', 'paid')->sum('total_amount'),
        ];

        $pendingProviders = Provider::query()
            ->where('verification_status', 'pending')
            ->with('user:id,first_name,last_name,email')
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (Provider $provider): array {
                return [
                    'id' => $provider->id,
                    'business_name' => $provider->business_name,
                    'owner_name' => trim(($provider->user?->first_name ?? '').' '.($provider->user?->last_name ?? '')),
                    'owner_email' => $provider->user?->email,
                    'created_at' => $provider->created_at?->toDateString(),
                ];
            });

        $recentUsers = User::query()
            ->latest()
            ->limit(8)
            ->get(['id', 'first_name', 'last_name', 'email', 'role', 'created_at'])
            ->map(function (User $user): array {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at?->toDateString(),
                ];
            });

        $recentBookings = Booking::query()
            ->with(['user:id,first_name,last_name', 'provider:id,business_name', 'variant.item:id,name,slug'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (Booking $booking): array {
                return [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                    'total_amount' => (float) $booking->total_amount,
                    'created_at' => $booking->created_at?->toDateString(),
                    'renter_name' => trim(($booking->user?->first_name ?? '').' '.($booking->user?->last_name ?? '')),
                    'provider_name' => $booking->provider?->business_name,
                    'item_name' => $booking->variant?->item?->name,
                ];
            });

        return Inertia::render('Dashboard/Admin', [
            'stats' => $stats,
            'pendingProviders' => $pendingProviders,
            'recentUsers' => $recentUsers,
            'recentBookings' => $recentBookings,
        ]);
    }
}


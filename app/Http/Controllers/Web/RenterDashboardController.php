<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RenterDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $stats = [
            'total_bookings' => Booking::where('user_id', $user->id)->count(),
            'upcoming_bookings' => Booking::where('user_id', $user->id)->upcoming()->count(),
            'active_bookings' => Booking::where('user_id', $user->id)->active()->count(),
            'wishlist_items' => $user->wishlistItems()->count(),
        ];

        $recentBookings = Booking::query()
            ->where('user_id', $user->id)
            ->with(['provider:id,business_name', 'variant.item:id,name,slug'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(function (Booking $booking): array {
                return [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                    'start_date' => $booking->start_date?->toDateString(),
                    'end_date' => $booking->end_date?->toDateString(),
                    'total_amount' => (float) $booking->total_amount,
                    'provider_name' => $booking->provider?->business_name,
                    'item_name' => $booking->variant?->item?->name,
                    'item_slug' => $booking->variant?->item?->slug,
                ];
            });

        $wishlist = $user->wishlistItems()
            ->with(['provider:id,business_name', 'primaryPhoto:id,item_id,photo_url'])
            ->orderByPivot('created_at', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($item): array {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'provider_name' => $item->provider?->business_name,
                    'photo_url' => $item->primaryPhoto?->photo_url,
                    'lowest_price' => $item->lowest_price,
                ];
            });

        return Inertia::render('Dashboard/Renter', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'wishlist' => $wishlist,
        ]);
    }
}


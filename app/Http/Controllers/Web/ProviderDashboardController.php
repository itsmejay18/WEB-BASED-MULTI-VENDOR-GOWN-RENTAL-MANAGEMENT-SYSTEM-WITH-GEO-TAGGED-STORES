<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProviderDashboardController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $provider = $request->user()?->provider;

        if (! $provider) {
            return redirect()
                ->route('home')
                ->with('error', 'Provider profile not found for this account.');
        }

        $stats = [
            'total_items' => Item::where('provider_id', $provider->id)->count(),
            'active_items' => Item::where('provider_id', $provider->id)->where('is_active', true)->count(),
            'total_bookings' => Booking::where('provider_id', $provider->id)->count(),
            'pending_bookings' => Booking::where('provider_id', $provider->id)->where('status', 'pending')->count(),
            'active_bookings' => Booking::where('provider_id', $provider->id)->active()->count(),
            'total_revenue' => (float) Booking::where('provider_id', $provider->id)
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
        ];

        $recentBookings = Booking::query()
            ->where('provider_id', $provider->id)
            ->with(['user:id,first_name,last_name', 'variant.item:id,name,slug'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (Booking $booking): array {
                return [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                    'start_date' => $booking->start_date?->toDateString(),
                    'end_date' => $booking->end_date?->toDateString(),
                    'total_amount' => (float) $booking->total_amount,
                    'renter_name' => trim(($booking->user?->first_name ?? '').' '.($booking->user?->last_name ?? '')),
                    'item_name' => $booking->variant?->item?->name,
                    'item_slug' => $booking->variant?->item?->slug,
                ];
            });

        $popularItems = Item::query()
            ->where('provider_id', $provider->id)
            ->with(['primaryPhoto:id,item_id,photo_url'])
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->limit(6)
            ->get()
            ->map(function (Item $item): array {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'bookings_count' => $item->bookings_count,
                    'is_active' => $item->is_active,
                    'photo_url' => $item->primaryPhoto?->photo_url,
                    'lowest_price' => $item->lowest_price,
                ];
            });

        return Inertia::render('Dashboard/Provider', [
            'provider' => [
                'id' => $provider->id,
                'business_name' => $provider->business_name,
                'verification_status' => $provider->verification_status,
                'rating' => (float) $provider->rating,
            ],
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'popularItems' => $popularItems,
        ]);
    }

    public function analytics(Request $request): Response|RedirectResponse
    {
        $provider = $request->user()?->provider;
        if (! $provider) {
            return redirect()->route('home')->with('error', 'Provider profile not found for this account.');
        }

        $monthlyRevenue = Booking::query()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
            ->selectRaw('SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as revenue')
            ->where('provider_id', $provider->id)
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(12)
            ->get()
            ->reverse()
            ->values();

        $statusBreakdown = Booking::query()
            ->selectRaw('status, COUNT(*) as total')
            ->where('provider_id', $provider->id)
            ->groupBy('status')
            ->pluck('total', 'status');

        $topItems = Item::query()
            ->where('provider_id', $provider->id)
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->limit(10)
            ->get(['id', 'name', 'slug', 'total_rentals']);

        return Inertia::render('Provider/Analytics/Index', [
            'monthlyRevenue' => $monthlyRevenue,
            'statusBreakdown' => $statusBreakdown,
            'topItems' => $topItems,
        ]);
    }
}

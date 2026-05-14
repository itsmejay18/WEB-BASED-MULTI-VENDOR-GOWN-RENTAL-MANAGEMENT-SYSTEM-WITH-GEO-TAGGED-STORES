<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, CacheRepository $cache): JsonResponse
    {
        $provider = $request->user()->provider;
        abort_unless($provider !== null, 403, 'Provider profile required.');

        $cacheKey = "provider:{$provider->id}:analytics";
        $data = $cache->remember($cacheKey, now()->addMinutes(30), function () use ($provider) {
            $bookings = Booking::where('provider_id', $provider->id);

            return [
                'total_bookings' => (clone $bookings)->count(),
                'upcoming_bookings' => (clone $bookings)->upcoming()->count(),
                'active_bookings' => (clone $bookings)->active()->count(),
                'completed_bookings' => (clone $bookings)->where('status', 'completed')->count(),
                'total_revenue' => (float) (clone $bookings)->where('payment_status', 'paid')->sum('total_amount'),
                'average_booking_value' => (float) (clone $bookings)->avg('total_amount'),
                'average_rating' => (float) $provider->rating,
            ];
        });

        return response()->json($data);
    }
}

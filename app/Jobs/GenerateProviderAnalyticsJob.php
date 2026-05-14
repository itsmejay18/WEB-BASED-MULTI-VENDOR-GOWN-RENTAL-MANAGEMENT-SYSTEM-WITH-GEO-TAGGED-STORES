<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Provider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateProviderAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $providerId) {}

    public function handle(CacheRepository $cache): void
    {
        $provider = Provider::find($this->providerId);
        if (! $provider) {
            return;
        }

        $bookings = Booking::where('provider_id', $provider->id);

        $analytics = [
            'total_bookings' => (clone $bookings)->count(),
            'upcoming_bookings' => (clone $bookings)->upcoming()->count(),
            'completed_bookings' => (clone $bookings)->where('status', 'completed')->count(),
            'total_revenue' => (float) (clone $bookings)->where('payment_status', 'paid')->sum('total_amount'),
            'average_booking_value' => (float) (clone $bookings)->avg('total_amount'),
            'generated_at' => now()->toIso8601String(),
        ];

        $cache->put("provider:{$provider->id}:analytics", $analytics, now()->addHours(6));
    }
}

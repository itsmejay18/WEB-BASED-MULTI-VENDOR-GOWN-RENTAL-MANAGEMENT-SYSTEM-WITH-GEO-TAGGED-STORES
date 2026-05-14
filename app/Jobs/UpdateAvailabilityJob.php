<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\AvailabilityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAvailabilityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $bookingId,
        public bool $isAvailable = false
    ) {}

    public function handle(AvailabilityService $availabilityService): void
    {
        $booking = Booking::with('variant')->find($this->bookingId);
        if (! $booking || ! $booking->variant) {
            return;
        }

        $availabilityService->blockAvailability(
            $booking->variant,
            $booking->start_date->toDateString(),
            $booking->end_date->toDateString(),
            $this->isAvailable,
            $this->isAvailable ? 'Released by booking update.' : 'Blocked by booking.'
        );
    }
}

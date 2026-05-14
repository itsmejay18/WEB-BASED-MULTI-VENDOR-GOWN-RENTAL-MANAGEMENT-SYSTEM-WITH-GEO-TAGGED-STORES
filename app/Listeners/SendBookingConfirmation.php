<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Jobs\SendBookingConfirmationJob;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBookingConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private readonly NotificationService $notificationService) {}

    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking->loadMissing('user');
        SendBookingConfirmationJob::dispatch($booking->id);

        if ($booking->user) {
            $this->notificationService->sendInApp(
                $booking->user,
                'booking_created',
                'Booking Created',
                "Your booking {$booking->booking_number} has been created.",
                ['booking_id' => $booking->id]
            );
        }
    }
}

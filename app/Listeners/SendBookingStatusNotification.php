<?php

namespace App\Listeners;

use App\Events\BookingStatusChanged;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBookingStatusNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private readonly NotificationService $notificationService) {}

    public function handle(BookingStatusChanged $event): void
    {
        $booking = $event->booking->loadMissing('user');
        if (! $booking->user) {
            return;
        }

        $body = "Booking {$booking->booking_number} changed from {$event->oldStatus} to {$event->newStatus}.";

        $this->notificationService->sendInApp(
            $booking->user,
            'booking_status_changed',
            'Booking Status Updated',
            $body,
            [
                'booking_id' => $booking->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
            ]
        );

        $this->notificationService->sendBookingStatusEmail($booking->user, [
            'booking_number' => $booking->booking_number,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
        ]);
    }
}

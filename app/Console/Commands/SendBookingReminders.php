<?php

namespace App\Console\Commands;

use App\Jobs\SendBookingReminderJob;
use App\Models\Booking;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    protected $signature = 'rentfit:send-booking-reminders';

    protected $description = 'Dispatch reminders for upcoming bookings.';

    public function handle(): int
    {
        $bookings = Booking::query()
            ->whereIn('status', ['approved', 'ready_for_pickup'])
            ->whereDate('start_date', now()->addDay()->toDateString())
            ->get();

        foreach ($bookings as $booking) {
            SendBookingReminderJob::dispatch($booking->id);
        }

        $this->info("Booking reminders dispatched: {$bookings->count()}");

        return self::SUCCESS;
    }
}

<?php

namespace App\Jobs;

use App\Mail\ReminderMail;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBookingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $bookingId) {}

    public function handle(): void
    {
        $booking = Booking::with('user')->find($this->bookingId);
        if (! $booking || ! $booking->user) {
            return;
        }

        Mail::to($booking->user->email)->send(new ReminderMail([
            'booking_number' => $booking->booking_number,
            'start_date' => $booking->start_date?->toDateString(),
            'end_date' => $booking->end_date?->toDateString(),
        ]));
    }
}

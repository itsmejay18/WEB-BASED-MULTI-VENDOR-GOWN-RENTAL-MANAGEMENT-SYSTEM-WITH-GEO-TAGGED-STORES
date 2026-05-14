<?php

namespace App\Jobs;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $bookingId) {}

    public function handle(): void
    {
        $booking = Booking::with(['user', 'provider', 'variant.item'])->find($this->bookingId);
        if (! $booking || ! $booking->user) {
            return;
        }

        Mail::to($booking->user->email)->send(new BookingConfirmationMail([
            'booking_number' => $booking->booking_number,
            'provider_name' => $booking->provider?->business_name,
            'start_date' => $booking->start_date?->toDateString(),
            'end_date' => $booking->end_date?->toDateString(),
            'total_amount' => $booking->total_amount,
        ]));
    }
}

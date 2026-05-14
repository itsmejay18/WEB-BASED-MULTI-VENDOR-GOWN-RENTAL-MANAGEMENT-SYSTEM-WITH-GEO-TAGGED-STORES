<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('role', 'renter')->doesntExist()) {
            return;
        }

        Booking::factory(30)->create()->each(function (Booking $booking) {
            $booking->timeline()->create([
                'status' => $booking->status,
                'notes' => 'Seeded booking timeline.',
                'created_by' => $booking->user_id,
            ]);

            if ($booking->payment_status === 'paid') {
                Payment::factory()->create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'amount' => $booking->total_amount,
                    'status' => 'succeeded',
                ]);
            }

            if (in_array($booking->status, ['completed', 'returned'], true)) {
                Review::factory()->create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'provider_id' => $booking->provider_id,
                    'item_id' => $booking->variant?->item_id,
                ]);
            }
        });
    }
}

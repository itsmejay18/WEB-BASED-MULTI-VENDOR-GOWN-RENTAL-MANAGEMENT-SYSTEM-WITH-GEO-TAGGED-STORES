<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class CheckOverdueBookings extends Command
{
    protected $signature = 'rentfit:check-overdue-bookings';

    protected $description = 'Mark active bookings as disputed when return date has passed.';

    public function handle(): int
    {
        $updated = Booking::query()
            ->whereIn('status', ['approved', 'ready_for_pickup', 'picked_up'])
            ->whereDate('end_date', '<', now()->toDateString())
            ->update(['status' => 'disputed']);

        $this->info("Overdue bookings updated: {$updated}");

        return self::SUCCESS;
    }
}

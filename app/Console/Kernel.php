<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('rentfit:check-overdue-bookings')->dailyAt('01:00');
        $schedule->command('rentfit:send-booking-reminders')->hourly();
        $schedule->command('rentfit:update-provider-ratings')->dailyAt('02:00');
        $schedule->command('rentfit:cleanup-old-notifications')->weeklyOn(1, '03:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

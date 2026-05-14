<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class CleanupOldNotifications extends Command
{
    protected $signature = 'rentfit:cleanup-old-notifications {--days=90 : Delete read notifications older than X days}';

    protected $description = 'Remove stale read notifications.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = Notification::query()
            ->where('is_read', true)
            ->whereDate('updated_at', '<', $cutoff->toDateString())
            ->delete();

        $this->info("Deleted {$deleted} notifications.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Provider;
use Illuminate\Console\Command;

class UpdateProviderRatings extends Command
{
    protected $signature = 'rentfit:update-provider-ratings';

    protected $description = 'Recalculate provider ratings from current reviews.';

    public function handle(): int
    {
        $providers = Provider::all();
        foreach ($providers as $provider) {
            $provider->updateRating();
        }

        $this->info("Updated ratings for {$providers->count()} providers.");

        return self::SUCCESS;
    }
}

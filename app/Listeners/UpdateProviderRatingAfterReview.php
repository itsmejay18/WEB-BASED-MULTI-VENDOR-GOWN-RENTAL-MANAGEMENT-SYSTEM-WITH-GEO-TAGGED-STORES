<?php

namespace App\Listeners;

use App\Events\ReviewSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateProviderRatingAfterReview implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReviewSubmitted $event): void
    {
        $provider = $event->review->provider;
        if ($provider) {
            $provider->updateRating();
        }
    }
}

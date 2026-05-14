<?php

namespace App\Providers;

use App\Events\BookingCreated;
use App\Events\BookingStatusChanged;
use App\Events\PaymentProcessed;
use App\Events\ReviewSubmitted;
use App\Listeners\SendBookingConfirmation;
use App\Listeners\SendBookingStatusNotification;
use App\Listeners\SendPaymentReceipt;
use App\Listeners\UpdateProviderRatingAfterReview;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        BookingCreated::class => [
            SendBookingConfirmation::class,
        ],
        BookingStatusChanged::class => [
            SendBookingStatusNotification::class,
        ],
        PaymentProcessed::class => [
            SendPaymentReceipt::class,
        ],
        ReviewSubmitted::class => [
            UpdateProviderRatingAfterReview::class,
        ],
    ];

    public function boot(): void {}

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

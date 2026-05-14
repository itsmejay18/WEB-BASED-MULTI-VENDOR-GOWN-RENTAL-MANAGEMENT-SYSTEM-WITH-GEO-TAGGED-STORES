<?php

return [
    'booking' => [
        'default_currency' => env('RENTFIT_DEFAULT_CURRENCY', 'PHP'),
        'booking_prefix' => env('RENTFIT_BOOKING_PREFIX', 'BK'),
    ],
    'provider' => [
        'default_commission_rate' => (float) env('RENTFIT_DEFAULT_COMMISSION_RATE', 10),
    ],
    'notification' => [
        'cleanup_days' => (int) env('RENTFIT_NOTIFICATION_CLEANUP_DAYS', 90),
    ],
];

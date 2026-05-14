<?php

return [
    'platform_fee_rate' => (float) env('PRICING_PLATFORM_FEE_RATE', 0.05),
    'cleaning_days' => (int) env('PRICING_CLEANING_DAYS', 1),
    'default_duration_days' => (int) env('PRICING_DEFAULT_DURATION_DAYS', 3),
];

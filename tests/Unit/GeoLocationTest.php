<?php

namespace Tests\Unit;

use App\Services\GeoLocationService;
use Tests\TestCase;

class GeoLocationTest extends TestCase
{
    public function test_calculate_distance_returns_zero_for_same_point(): void
    {
        $service = new GeoLocationService;
        $distance = $service->calculateDistanceKm(14.5995, 120.9842, 14.5995, 120.9842);

        $this->assertSame(0.0, $distance);
    }

    public function test_is_within_radius_works(): void
    {
        $service = new GeoLocationService;
        $result = $service->isWithinRadius(14.5995, 120.9842, 14.6095, 120.9942, 5);

        $this->assertTrue($result);
    }
}

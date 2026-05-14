<?php

namespace App\Services;

class GeoLocationService
{
    public function calculateDistanceKm(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo): float
    {
        $earthRadiusKm = 6371;

        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($earthRadiusKm * $angle, 3);
    }

    public function isWithinRadius(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo, float $radiusKm): bool
    {
        return $this->calculateDistanceKm($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo) <= $radiusKm;
    }
}

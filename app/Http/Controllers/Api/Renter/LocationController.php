<?php

namespace App\Http\Controllers\Api\Renter;

use App\Http\Controllers\Controller;
use App\Models\ProviderLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function nearby(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:100'],
        ]);

        $radius = (float) ($data['radius_km'] ?? config('geolocation.default_radius_km', 10));

        $locations = ProviderLocation::query()
            ->active()
            ->withinDistance((float) $data['latitude'], (float) $data['longitude'], $radius)
            ->orderByDistance((float) $data['latitude'], (float) $data['longitude'])
            ->with('provider:id,business_name,rating,verification_status')
            ->limit(100)
            ->get()
            ->map(function (ProviderLocation $location): array {
                return [
                    'id' => $location->id,
                    'location_name' => $location->location_name,
                    'full_address' => $location->full_address,
                    'latitude' => (float) $location->latitude,
                    'longitude' => (float) $location->longitude,
                    'city' => $location->city,
                    'service_radius_km' => (int) $location->service_radius_km,
                    'provider' => [
                        'id' => $location->provider?->id,
                        'business_name' => $location->provider?->business_name,
                        'rating' => (float) ($location->provider?->rating ?? 0),
                        'verification_status' => $location->provider?->verification_status,
                    ],
                ];
            });

        return response()->json(['locations' => $locations]);
    }
}


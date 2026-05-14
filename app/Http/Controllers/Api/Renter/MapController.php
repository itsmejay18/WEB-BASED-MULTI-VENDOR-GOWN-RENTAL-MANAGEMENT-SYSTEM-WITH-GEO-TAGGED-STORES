<?php

namespace App\Http\Controllers\Api\Renter;

use App\Http\Controllers\Controller;
use App\Models\ProviderLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'keyword' => ['nullable', 'string', 'max:255'],
        ]);

        $radius = (float) ($data['radius_km'] ?? config('geolocation.default_radius_km', 10));

        $locations = ProviderLocation::query()
            ->active()
            ->withinDistance((float) $data['latitude'], (float) $data['longitude'], $radius)
            ->orderByDistance((float) $data['latitude'], (float) $data['longitude'])
            ->with(['provider:id,business_name', 'provider.items.primaryPhoto:id,item_id,photo_url'])
            ->limit(120)
            ->get();

        $markers = $locations->flatMap(function (ProviderLocation $location) use ($data) {
            return $location->provider->items
                ->filter(function ($item) use ($data) {
                    if (empty($data['keyword'])) {
                        return true;
                    }

                    return str_contains(strtolower($item->name), strtolower($data['keyword']));
                })
                ->map(function ($item) use ($location) {
                    return [
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'item_slug' => $item->slug,
                        'provider_name' => $location->provider?->business_name,
                        'latitude' => (float) $location->latitude,
                        'longitude' => (float) $location->longitude,
                        'photo_url' => $item->primaryPhoto?->photo_url,
                    ];
                });
        })->values();

        return response()->json(['markers' => $markers]);
    }
}


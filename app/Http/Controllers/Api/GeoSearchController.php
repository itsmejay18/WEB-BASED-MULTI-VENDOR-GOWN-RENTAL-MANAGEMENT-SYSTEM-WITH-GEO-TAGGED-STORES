<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Provider;
use App\Models\ProviderLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeoSearchController extends Controller
{
    public function nearby(Request $request): JsonResponse
    {
        $data = $this->validateRequest($request);

        $providers = $this->providers($request)->getData(true);
        $items = $this->items($request)->getData(true);

        return response()->json([
            'center' => [
                'latitude' => (float) $data['latitude'],
                'longitude' => (float) $data['longitude'],
                'radius_km' => (float) ($data['radius_km'] ?? 10),
            ],
            'providers' => $providers['providers'] ?? [],
            'items' => $items['items'] ?? [],
        ]);
    }

    public function providers(Request $request): JsonResponse
    {
        $data = $this->validateRequest($request);
        $radius = (float) ($data['radius_km'] ?? config('geolocation.default_radius_km', 10));

        $locations = ProviderLocation::query()
            ->active()
            ->withinDistance((float) $data['latitude'], (float) $data['longitude'], $radius)
            ->orderByDistance((float) $data['latitude'], (float) $data['longitude'])
            ->with('provider.mainLocation')
            ->get();

        $providers = $locations
            ->pluck('provider')
            ->filter()
            ->unique('id')
            ->values()
            ->map(function (Provider $provider) {
                return [
                    'id' => $provider->id,
                    'business_name' => $provider->business_name,
                    'rating' => (float) $provider->rating,
                    'verification_status' => $provider->verification_status,
                    'main_location' => $provider->mainLocation ? [
                        'latitude' => (float) $provider->mainLocation->latitude,
                        'longitude' => (float) $provider->mainLocation->longitude,
                        'city' => $provider->mainLocation->city,
                    ] : null,
                ];
            });

        return response()->json(['providers' => $providers]);
    }

    public function items(Request $request): JsonResponse
    {
        $data = $this->validateRequest($request);
        $radius = (float) ($data['radius_km'] ?? config('geolocation.default_radius_km', 10));

        $items = Item::query()
            ->with(['provider.mainLocation', 'primaryPhoto', 'pricingTiers'])
            ->where('is_active', true)
            ->whereHas('provider.locations', function ($query) use ($data, $radius) {
                $query->active()->withinDistance((float) $data['latitude'], (float) $data['longitude'], $radius);
            })
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = $request->string('keyword')->toString();
                $query->where(function ($nestedQuery) use ($keyword) {
                    $nestedQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%")
                        ->orWhere('brand', 'like', "%{$keyword}%");
                });
            })
            ->limit($request->integer('limit', 100))
            ->get()
            ->map(function (Item $item): array {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'brand' => $item->brand,
                    'lowest_price' => $item->lowest_price,
                    'photo_url' => $item->primaryPhoto?->photo_url,
                    'provider' => [
                        'id' => $item->provider?->id,
                        'business_name' => $item->provider?->business_name,
                        'main_location' => $item->provider?->mainLocation ? [
                            'latitude' => (float) $item->provider->mainLocation->latitude,
                            'longitude' => (float) $item->provider->mainLocation->longitude,
                            'city' => $item->provider->mainLocation->city,
                        ] : null,
                    ],
                ];
            });

        return response()->json(['items' => $items]);
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'keyword' => ['nullable', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);
    }
}


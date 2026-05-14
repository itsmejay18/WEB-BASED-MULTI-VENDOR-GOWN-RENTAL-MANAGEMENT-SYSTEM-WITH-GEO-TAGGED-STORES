<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $provider = $request->user()?->provider;
        abort_unless($provider, 404, 'Provider profile not found.');

        $locations = ProviderLocation::query()
            ->where('provider_id', $provider->id)
            ->orderByDesc('is_main')
            ->orderBy('city')
            ->get();

        return response()->json(['locations' => $locations]);
    }

    public function geocode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address' => ['required', 'string', 'max:255'],
        ]);

        $endpoint = config('services.nominatim.url', 'https://nominatim.openstreetmap.org/search');

        $response = Http::withHeaders([
            'User-Agent' => config('app.name', 'RentFit').'/1.0',
        ])->get($endpoint, [
            'q' => $data['address'],
            'format' => 'json',
            'limit' => 1,
        ]);

        if (! $response->ok()) {
            return response()->json(['message' => 'Unable to geocode address at this time.'], 422);
        }

        $results = $response->json();
        if (empty($results)) {
            return response()->json(['message' => 'No location match found.'], 404);
        }

        $top = $results[0];

        return response()->json([
            'latitude' => (float) $top['lat'],
            'longitude' => (float) $top['lon'],
            'display_name' => $top['display_name'] ?? null,
        ]);
    }
}


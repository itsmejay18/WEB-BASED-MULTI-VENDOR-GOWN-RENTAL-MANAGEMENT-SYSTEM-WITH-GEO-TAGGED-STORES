<?php

namespace App\Http\Controllers\Web\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $provider = $request->user()?->provider;
        if (! $provider) {
            return redirect()->route('provider.dashboard')->with('error', 'Provider profile not found.');
        }

        $locations = ProviderLocation::query()
            ->where('provider_id', $provider->id)
            ->with('businessHours')
            ->orderByDesc('is_main')
            ->orderBy('city')
            ->get()
            ->map(function (ProviderLocation $location): array {
                return [
                    'id' => $location->id,
                    'location_name' => $location->location_name,
                    'address_line1' => $location->address_line1,
                    'address_line2' => $location->address_line2,
                    'city' => $location->city,
                    'state' => $location->state,
                    'postal_code' => $location->postal_code,
                    'country' => $location->country,
                    'latitude' => $location->latitude ? (float) $location->latitude : null,
                    'longitude' => $location->longitude ? (float) $location->longitude : null,
                    'service_radius_km' => (int) $location->service_radius_km,
                    'is_main' => $location->is_main,
                    'is_active' => $location->is_active,
                    'business_hours' => $location->businessHours,
                ];
            });

        return Inertia::render('Provider/Locations/Index', [
            'locations' => $locations,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $provider = $request->user()?->provider;
        abort_unless($provider, 404, 'Provider profile not found.');

        $data = $request->validate([
            'location_name' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'service_radius_km' => ['nullable', 'integer', 'min:1', 'max:200'],
            'is_main' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['is_main'])) {
            ProviderLocation::where('provider_id', $provider->id)->update(['is_main' => false]);
        }

        ProviderLocation::create([
            'provider_id' => $provider->id,
            'location_name' => $data['location_name'] ?? null,
            'address_line1' => $data['address_line1'],
            'address_line2' => $data['address_line2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? 'Philippines',
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'service_radius_km' => $data['service_radius_km'] ?? 10,
            'is_main' => $data['is_main'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return back()->with('success', 'Location saved.');
    }

    public function update(Request $request, ProviderLocation $location): RedirectResponse
    {
        $this->assertProviderOwnsLocation($request, $location);

        $data = $request->validate([
            'location_name' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'service_radius_km' => ['nullable', 'integer', 'min:1', 'max:200'],
            'is_main' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['is_main'])) {
            ProviderLocation::where('provider_id', $location->provider_id)
                ->where('id', '!=', $location->id)
                ->update(['is_main' => false]);
        }

        $location->update([
            'location_name' => $data['location_name'] ?? null,
            'address_line1' => $data['address_line1'],
            'address_line2' => $data['address_line2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? 'Philippines',
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'service_radius_km' => $data['service_radius_km'] ?? 10,
            'is_main' => $data['is_main'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return back()->with('success', 'Location updated.');
    }

    public function destroy(Request $request, ProviderLocation $location): RedirectResponse
    {
        $this->assertProviderOwnsLocation($request, $location);

        if ($location->pickupBookings()->exists()) {
            return back()->with('error', 'Cannot delete location with existing pickup bookings.');
        }

        $location->delete();

        return back()->with('success', 'Location removed.');
    }

    private function assertProviderOwnsLocation(Request $request, ProviderLocation $location): void
    {
        $providerId = $request->user()?->provider?->id;
        abort_if((int) $location->provider_id !== (int) $providerId, 403, 'Unauthorized location action.');
    }
}


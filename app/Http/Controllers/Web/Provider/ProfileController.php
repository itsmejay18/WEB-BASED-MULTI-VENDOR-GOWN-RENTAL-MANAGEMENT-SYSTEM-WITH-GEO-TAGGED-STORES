<?php

namespace App\Http\Controllers\Web\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response|RedirectResponse
    {
        $provider = $request->user()?->provider;
        if (! $provider) {
            return redirect()->route('provider.dashboard')->with('error', 'Provider profile not found.');
        }

        $provider->load('user:id,first_name,last_name,email,phone');

        return Inertia::render('Provider/Profile/Edit', [
            'provider' => $provider,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $provider = $request->user()?->provider;
        abort_unless($provider, 404, 'Provider profile not found.');

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_registration' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($request->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'cover_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        DB::transaction(function () use ($request, $provider, $validated): void {
            $request->user()->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);

            $providerPayload = [
                'business_name' => $validated['business_name'],
                'business_registration' => $validated['business_registration'] ?? null,
                'description' => $validated['description'] ?? null,
            ];

            if ($request->hasFile('logo')) {
                if (! empty($provider->logo)) {
                    Storage::disk('public')->delete($provider->logo);
                }
                $providerPayload['logo'] = $request->file('logo')->store('providers/logos', 'public');
            }

            if ($request->hasFile('cover_photo')) {
                if (! empty($provider->cover_photo)) {
                    Storage::disk('public')->delete($provider->cover_photo);
                }
                $providerPayload['cover_photo'] = $request->file('cover_photo')->store('providers/covers', 'public');
            }

            $provider->update($providerPayload);
        });

        return back()->with('success', 'Provider profile updated.');
    }

    public function businessHours(Request $request): Response|RedirectResponse
    {
        $provider = $request->user()?->provider;
        if (! $provider) {
            return redirect()->route('provider.dashboard')->with('error', 'Provider profile not found.');
        }

        $locations = $provider->locations()
            ->with('businessHours')
            ->orderByDesc('is_main')
            ->orderBy('location_name')
            ->get()
            ->map(function (ProviderLocation $location): array {
                return [
                    'id' => $location->id,
                    'location_name' => $location->location_name,
                    'city' => $location->city,
                    'state' => $location->state,
                    'country' => $location->country,
                    'business_hours' => $location->businessHours,
                ];
            });

        return Inertia::render('Provider/Profile/BusinessHours', [
            'locations' => $locations,
        ]);
    }

    public function updateBusinessHours(Request $request, ProviderLocation $location): RedirectResponse
    {
        $provider = $request->user()?->provider;
        abort_if(! $provider || (int) $provider->id !== (int) $location->provider_id, 403, 'Unauthorized business hours action.');

        $validated = $request->validate([
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'hours.*.open_time' => ['nullable', 'date_format:H:i'],
            'hours.*.close_time' => ['nullable', 'date_format:H:i'],
            'hours.*.is_closed' => ['nullable', 'boolean'],
        ]);

        foreach ($validated['hours'] as $hour) {
            if (! ($hour['is_closed'] ?? false) && ! empty($hour['open_time']) && ! empty($hour['close_time']) && $hour['close_time'] <= $hour['open_time']) {
                return back()->withErrors([
                    'hours' => 'Closing time must be later than opening time.',
                ]);
            }
        }

        DB::transaction(function () use ($location, $validated): void {
            $location->businessHours()->delete();

            $rows = array_map(function (array $hour): array {
                return [
                    'day_of_week' => $hour['day_of_week'],
                    'open_time' => ($hour['is_closed'] ?? false) ? null : ($hour['open_time'] ?? null),
                    'close_time' => ($hour['is_closed'] ?? false) ? null : ($hour['close_time'] ?? null),
                    'is_closed' => (bool) ($hour['is_closed'] ?? false),
                ];
            }, $validated['hours']);

            $location->businessHours()->createMany($rows);
        });

        return back()->with('success', 'Business hours updated.');
    }
}

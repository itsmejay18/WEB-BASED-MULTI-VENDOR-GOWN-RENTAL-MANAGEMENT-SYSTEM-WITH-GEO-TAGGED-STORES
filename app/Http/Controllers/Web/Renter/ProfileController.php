<?php

namespace App\Http\Controllers\Web\Renter;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user()->load(['addresses', 'measurement']);

        return Inertia::render('User/Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_photo' => $user->profile_photo,
                'created_at' => $user->created_at?->toDateTimeString(),
                'addresses_count' => $user->addresses->count(),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('profile_photo')) {
            if (! empty($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('users/profiles', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function measurements(Request $request): Response
    {
        $measurement = $request->user()->measurement;

        return Inertia::render('User/Profile/Measurements', [
            'measurements' => $measurement,
        ]);
    }

    public function updateMeasurements(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'height_cm' => ['nullable', 'numeric', 'min:50', 'max:250'],
            'weight_kg' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'chest_cm' => ['nullable', 'numeric', 'min:50', 'max:200'],
            'waist_cm' => ['nullable', 'numeric', 'min:40', 'max:200'],
            'hips_cm' => ['nullable', 'numeric', 'min:50', 'max:200'],
            'inseam_cm' => ['nullable', 'numeric', 'min:40', 'max:150'],
            'shoulder_width_cm' => ['nullable', 'numeric', 'min:20', 'max:100'],
            'dress_size' => ['nullable', 'string', 'max:20'],
            'pant_size' => ['nullable', 'string', 'max:20'],
            'shirt_size' => ['nullable', 'string', 'max:20'],
            'shoe_size' => ['nullable', 'string', 'max:10'],
        ]);

        $request->user()->measurement()->updateOrCreate(
            ['user_id' => $request->user()->id],
            array_merge($validated, ['last_updated' => now()])
        );

        return back()->with('success', 'Measurements updated.');
    }

    public function addresses(Request $request): Response
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->latest()->get();

        return Inertia::render('User/Profile/Addresses', [
            'addresses' => $addresses,
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
            'address_type' => ['required', Rule::in(['home', 'work', 'other'])],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            if (($validated['is_default'] ?? false) === true) {
                $request->user()->addresses()->update(['is_default' => false]);
            }

            $request->user()->addresses()->create($validated);
        });

        return back()->with('success', 'Address added.');
    }

    public function updateAddress(Request $request, UserAddress $address): RedirectResponse
    {
        $this->assertAddressOwner($request, $address);

        $validated = $request->validate([
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
            'address_type' => ['required', Rule::in(['home', 'work', 'other'])],
        ]);

        DB::transaction(function () use ($address, $validated): void {
            if (($validated['is_default'] ?? false) === true) {
                $address->user->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $address->update($validated);
        });

        return back()->with('success', 'Address updated.');
    }

    public function destroyAddress(Request $request, UserAddress $address): RedirectResponse
    {
        $this->assertAddressOwner($request, $address);
        $address->delete();

        return back()->with('success', 'Address deleted.');
    }

    private function assertAddressOwner(Request $request, UserAddress $address): void
    {
        abort_if((int) $address->user_id !== (int) $request->user()->id, 403, 'Unauthorized address action.');
    }
}

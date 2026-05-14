<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\LocationResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()?->load(['addresses', 'measurement', 'provider']);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return response()->json([
            'user' => new UserResource($user->fresh(['addresses', 'measurement', 'provider'])),
        ]);
    }

    public function measurements(Request $request): JsonResponse
    {
        return response()->json([
            'measurement' => $request->user()?->measurement,
        ]);
    }

    public function updateMeasurements(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'height_cm' => ['nullable', 'numeric', 'min:0'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'chest_cm' => ['nullable', 'numeric', 'min:0'],
            'waist_cm' => ['nullable', 'numeric', 'min:0'],
            'hips_cm' => ['nullable', 'numeric', 'min:0'],
            'inseam_cm' => ['nullable', 'numeric', 'min:0'],
            'shoulder_width_cm' => ['nullable', 'numeric', 'min:0'],
            'dress_size' => ['nullable', 'string', 'max:20'],
            'pant_size' => ['nullable', 'string', 'max:20'],
            'shirt_size' => ['nullable', 'string', 'max:20'],
            'shoe_size' => ['nullable', 'string', 'max:10'],
        ]);

        $measurement = $request->user()->measurement()->updateOrCreate(
            ['user_id' => $request->user()->id],
            array_merge($validated, ['last_updated' => now()])
        );

        return response()->json(['measurement' => $measurement]);
    }

    public function addresses(Request $request): JsonResponse
    {
        return response()->json([
            'addresses' => LocationResource::collection($request->user()->addresses),
        ]);
    }

    public function addAddress(Request $request): JsonResponse
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
            'address_type' => ['nullable', 'in:home,work,other'],
        ]);

        if (($validated['is_default'] ?? false) === true) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create($validated);

        return response()->json([
            'address' => new LocationResource($address),
        ], 201);
    }

    public function updateAddress(Request $request, int $addressId): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($addressId);

        $validated = $request->validate([
            'address_line1' => ['sometimes', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
            'address_type' => ['nullable', 'in:home,work,other'],
        ]);

        if (($validated['is_default'] ?? false) === true) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'address' => new LocationResource($address->fresh()),
        ]);
    }

    public function deleteAddress(Request $request, int $addressId): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($addressId);
        $address->delete();

        return response()->json(['message' => 'Address deleted.']);
    }
}

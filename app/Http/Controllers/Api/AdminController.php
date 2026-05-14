<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users(Request $request): JsonResponse
    {
        $users = User::query()->latest()->paginate($request->integer('per_page', 20));

        return response()->json($users);
    }

    public function providers(Request $request): JsonResponse
    {
        $providers = Provider::query()
            ->with('user')
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json($providers);
    }

    public function pendingProviders(Request $request): JsonResponse
    {
        $providers = Provider::query()
            ->with('user')
            ->pending()
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json($providers);
    }

    public function approveProvider(Provider $provider): JsonResponse
    {
        $provider->update(['verification_status' => 'verified']);

        return response()->json([
            'message' => 'Provider approved.',
            'provider' => $provider->fresh(),
        ]);
    }

    public function rejectProvider(Request $request, Provider $provider): JsonResponse
    {
        $provider->update(['verification_status' => 'rejected']);

        return response()->json([
            'message' => 'Provider rejected.',
            'provider' => $provider->fresh(),
        ]);
    }

    public function analytics(): JsonResponse
    {
        return response()->json([
            'users_count' => User::count(),
            'providers_count' => Provider::count(),
            'verified_providers_count' => Provider::verified()->count(),
            'bookings_count' => Booking::count(),
            'paid_revenue' => (float) Booking::where('payment_status', 'paid')->sum('total_amount'),
        ]);
    }
}

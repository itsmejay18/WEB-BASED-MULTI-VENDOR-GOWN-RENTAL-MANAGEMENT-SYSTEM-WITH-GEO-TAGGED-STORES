<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ProviderResource;
use App\Http\Resources\ReviewResource;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Provider::query()
            ->with(['mainLocation', 'user'])
            ->verified();

        if ($request->filled('keyword')) {
            $query->where('business_name', 'like', '%'.$request->string('keyword')->toString().'%');
        }

        if ($request->boolean('top_rated')) {
            $query->topRated();
        }

        $providers = $query->paginate($request->integer('per_page', 20));

        return ProviderResource::collection($providers)->response();
    }

    public function show(Provider $provider): JsonResponse
    {
        $provider->load(['user', 'locations.businessHours', 'items.primaryPhoto']);

        return response()->json([
            'provider' => new ProviderResource($provider),
        ]);
    }

    public function items(Provider $provider, Request $request): JsonResponse
    {
        $items = $provider->items()
            ->with(['primaryPhoto', 'pricingTiers', 'variants'])
            ->active()
            ->paginate($request->integer('per_page', 20));

        return ItemResource::collection($items)->response();
    }

    public function reviews(Provider $provider, Request $request): JsonResponse
    {
        $reviews = $provider->reviews()
            ->with(['user', 'item'])
            ->where('is_public', true)
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return ReviewResource::collection($reviews)->response();
    }
}

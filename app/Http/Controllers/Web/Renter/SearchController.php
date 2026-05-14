<?php

namespace App\Http\Controllers\Web\Renter;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProviderLocation;
use App\Models\Tag;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __construct(private readonly SearchService $searchService) {}

    public function index(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'keyword' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'size' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:100'],
            'occasion' => ['nullable', 'string', 'max:100'],
            'tag' => ['nullable', 'string', 'max:120', 'exists:tags,slug'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:50'],
            'sort_by' => ['nullable', 'in:distance,relevance,price_low,price_high,rating,newest'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:40'],
        ]);
        $validator->after(function ($validator) use ($request): void {
            if (! $request->filled('min_price') || ! $request->filled('max_price')) {
                return;
            }

            if ((float) $request->input('max_price') < (float) $request->input('min_price')) {
                $validator->errors()->add('max_price', 'The max price must be greater than or equal to min price.');
            }
        });

        $filters = $validator->validate();

        $items = $this->searchService->searchItems(array_merge($filters, ['per_page' => $filters['per_page'] ?? 12]));
        $items->withQueryString();

        $items->through(function ($item): array {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'brand' => $item->brand,
                'condition_rating' => $item->condition_rating,
                'lowest_price' => $item->lowest_price,
                'rating' => isset($item->reviews_avg_rating) ? round((float) $item->reviews_avg_rating, 1) : null,
                'distance' => isset($item->distance) ? round((float) $item->distance, 2) : null,
                'provider' => [
                    'id' => $item->provider?->id,
                    'business_name' => $item->provider?->business_name,
                    'main_location' => $item->provider?->mainLocation ? [
                        'latitude' => (float) $item->provider->mainLocation->latitude,
                        'longitude' => (float) $item->provider->mainLocation->longitude,
                        'city' => $item->provider->mainLocation->city,
                    ] : null,
                ],
                'primary_photo' => $item->primaryPhoto,
                'primary_photo_url' => $item->primaryPhoto?->photo_url,
                'tags' => $item->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'type' => $tag->type,
                ])->values(),
            ];
        });

        $categories = Category::active()
            ->withCount('items')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        $tags = Tag::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'type']);

        return Inertia::render('Renter/Search', [
            'items' => $items,
            'categories' => $categories,
            'tags' => $tags,
            'filters' => $filters,
            'userLocation' => [
                'latitude' => $filters['latitude'] ?? null,
                'longitude' => $filters['longitude'] ?? null,
            ],
        ]);
    }

    public function map(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:50'],
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
                        'provider_id' => $location->provider_id,
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

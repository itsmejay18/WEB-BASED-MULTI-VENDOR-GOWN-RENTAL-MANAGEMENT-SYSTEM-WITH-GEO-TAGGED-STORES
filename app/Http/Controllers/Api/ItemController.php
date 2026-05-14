<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availabilityService,
        private readonly ItemRepositoryInterface $itemRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->itemRepository->paginated(
            $request->only(['category_id', 'provider_id', 'size', 'brand', 'keyword']),
            $request->integer('per_page', 20)
        );

        return ItemResource::collection($items)->response();
    }

    public function store(CreateItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $providerId = $request->user()?->provider?->id;

        if ($request->user()?->role === 'provider' && $providerId !== (int) $data['provider_id']) {
            abort(403, 'You can only create items for your own provider account.');
        }

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']).'-'.Str::lower(Str::random(6));
        $item = Item::create($data);

        return response()->json([
            'item' => new ItemResource($item->load(['provider', 'category'])),
        ], 201);
    }

    public function show(Item $item): JsonResponse
    {
        $item->load(['provider.mainLocation', 'category', 'variants.photos', 'photos', 'pricingTiers', 'tags']);

        return response()->json([
            'item' => new ItemResource($item),
        ]);
    }

    public function update(UpdateItemRequest $request, Item $item): JsonResponse
    {
        $providerId = $request->user()?->provider?->id;
        $isAdmin = $request->user()?->role === 'admin';
        abort_if(! $isAdmin && $item->provider_id !== $providerId, 403, 'Unauthorized item update.');

        $item->update($request->validated());

        return response()->json([
            'item' => new ItemResource($item->fresh(['provider', 'category', 'variants', 'pricingTiers'])),
        ]);
    }

    public function destroy(Request $request, Item $item): JsonResponse
    {
        $providerId = $request->user()?->provider?->id;
        $isAdmin = $request->user()?->role === 'admin';
        abort_if(! $isAdmin && $item->provider_id !== $providerId, 403, 'Unauthorized item delete.');

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully.']);
    }

    public function checkAvailability(Request $request, Item $item): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'variant_id' => ['nullable', 'integer', 'exists:item_variants,id'],
        ]);

        $variants = $item->variants()->active()->when(
            ! empty($validated['variant_id']),
            fn ($query) => $query->where('id', $validated['variant_id'])
        )->get();

        $availability = $variants->map(function ($variant) use ($validated) {
            return [
                'variant_id' => $variant->id,
                'available' => $this->availabilityService->isVariantAvailable(
                    $variant,
                    $validated['start_date'],
                    $validated['end_date'],
                    (int) config('pricing.cleaning_days', 1)
                ),
                'quantity_available' => $variant->quantity_available,
            ];
        });

        return response()->json([
            'item_id' => $item->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'availability' => $availability,
        ]);
    }
}

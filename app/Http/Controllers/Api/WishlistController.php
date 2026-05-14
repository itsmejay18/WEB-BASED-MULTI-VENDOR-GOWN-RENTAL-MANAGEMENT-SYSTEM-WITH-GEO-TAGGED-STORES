<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()
            ->wishlistItems()
            ->with(['provider.mainLocation', 'primaryPhoto', 'pricingTiers'])
            ->paginate($request->integer('per_page', 20));

        return ItemResource::collection($items)->response();
    }

    public function add(Request $request, Item $item): JsonResponse
    {
        $request->user()->wishlistItems()->syncWithoutDetaching([
            $item->id => ['notes' => $request->input('notes')],
        ]);

        return response()->json(['message' => 'Added to wishlist.'], 201);
    }

    public function remove(Request $request, Item $item): JsonResponse
    {
        $request->user()->wishlistItems()->detach($item->id);

        return response()->json(['message' => 'Removed from wishlist.']);
    }
}

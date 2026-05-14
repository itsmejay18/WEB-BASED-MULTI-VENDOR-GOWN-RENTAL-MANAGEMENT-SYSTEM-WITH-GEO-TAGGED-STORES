<?php

namespace App\Http\Controllers\Web\Renter;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function index(Request $request): Response
    {
        $items = $request->user()
            ->wishlistItems()
            ->with(['provider:id,business_name', 'primaryPhoto:id,item_id,photo_url', 'pricingTiers'])
            ->paginate(20)
            ->through(function (Item $item): array {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'brand' => $item->brand,
                    'provider_name' => $item->provider?->business_name,
                    'photo_url' => $item->primaryPhoto?->photo_url,
                    'lowest_price' => $item->lowest_price,
                ];
            });

        return Inertia::render('Renter/Wishlist/Index', [
            'items' => $items,
        ]);
    }

    public function store(Request $request, Item $item): RedirectResponse
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $request->user()->wishlistItems()->syncWithoutDetaching([
            $item->id => ['notes' => $request->input('notes')],
        ]);

        return back()->with('success', 'Item added to wishlist.');
    }

    public function destroy(Request $request, Item $item): RedirectResponse
    {
        $request->user()->wishlistItems()->detach($item->id);

        return back()->with('success', 'Item removed from wishlist.');
    }
}


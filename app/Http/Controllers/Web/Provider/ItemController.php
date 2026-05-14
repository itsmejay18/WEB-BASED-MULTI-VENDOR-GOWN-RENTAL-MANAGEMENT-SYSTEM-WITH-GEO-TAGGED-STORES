<?php

namespace App\Http\Controllers\Web\Provider;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\PricingTier;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $provider = $request->user()?->provider;
        if (! $provider) {
            return redirect()->route('provider.dashboard')->with('error', 'Provider profile not found.');
        }

        $items = Item::query()
            ->where('provider_id', $provider->id)
            ->with(['category:id,name', 'primaryPhoto:id,item_id,photo_url', 'variants'])
            ->latest()
            ->paginate(12)
            ->through(function (Item $item): array {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'category' => $item->category?->name,
                    'condition_rating' => $item->condition_rating,
                    'is_active' => $item->is_active,
                    'variants_count' => $item->variants->count(),
                    'photo_url' => $item->primaryPhoto?->photo_url,
                    'lowest_price' => $item->lowest_price,
                    'created_at' => $item->created_at?->toDateString(),
                ];
            });

        return Inertia::render('Provider/Items/Index', [
            'items' => $items,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Provider/Items/Create', [
            'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $provider = $request->user()?->provider;
        abort_unless($provider, 404, 'Provider profile not found.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:100'],
            'designer' => ['nullable', 'string', 'max:100'],
            'condition_rating' => ['required', 'in:new,like_new,good,fair'],
            'cleaning_policy' => ['nullable', 'string'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'late_fee_per_day' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'requires_approval' => ['nullable', 'boolean'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'price_day' => ['required', 'numeric', 'min:1'],
            'price_3_days' => ['nullable', 'numeric', 'min:1'],
            'price_7_days' => ['nullable', 'numeric', 'min:1'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.size_label' => ['required', 'string', 'max:50'],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.material' => ['nullable', 'string', 'max:100'],
            'variants.*.quantity_available' => ['required', 'integer', 'min:0'],
            'variants.*.chest_cm' => ['nullable', 'numeric', 'min:0'],
            'variants.*.waist_cm' => ['nullable', 'numeric', 'min:0'],
            'variants.*.length_cm' => ['nullable', 'numeric', 'min:0'],
            'variants.*.inseam_cm' => ['nullable', 'numeric', 'min:0'],
            'variants.*.shoulder_cm' => ['nullable', 'numeric', 'min:0'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:5120'],
        ]);

        DB::transaction(function () use ($provider, $data, $request): void {
            $slugBase = Str::slug($data['name']);
            $slug = $slugBase.'-'.Str::lower(Str::random(6));

            $item = Item::create([
                'provider_id' => $provider->id,
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'brand' => $data['brand'] ?? null,
                'designer' => $data['designer'] ?? null,
                'condition_rating' => $data['condition_rating'],
                'cleaning_policy' => $data['cleaning_policy'] ?? null,
                'security_deposit' => $data['security_deposit'] ?? 0,
                'late_fee_per_day' => $data['late_fee_per_day'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
                'requires_approval' => $data['requires_approval'] ?? false,
            ]);

            if (! empty($data['tag_ids'])) {
                $item->tags()->sync($data['tag_ids']);
            }

            foreach ($data['variants'] as $index => $variantData) {
                $item->variants()->create([
                    'sku' => strtoupper('RF-'.$item->id.'-'.($index + 1).'-'.Str::random(4)),
                    'size_label' => $variantData['size_label'],
                    'color' => $variantData['color'] ?? null,
                    'material' => $variantData['material'] ?? null,
                    'chest_cm' => $variantData['chest_cm'] ?? null,
                    'waist_cm' => $variantData['waist_cm'] ?? null,
                    'length_cm' => $variantData['length_cm'] ?? null,
                    'inseam_cm' => $variantData['inseam_cm'] ?? null,
                    'shoulder_cm' => $variantData['shoulder_cm'] ?? null,
                    'quantity_available' => $variantData['quantity_available'],
                    'is_active' => true,
                ]);
            }

            $tiers = [
                ['duration_days' => 1, 'price' => $data['price_day'], 'is_active' => true],
                ['duration_days' => 3, 'price' => $data['price_3_days'] ?? round($data['price_day'] * 2.5, 2), 'is_active' => true],
                ['duration_days' => 7, 'price' => $data['price_7_days'] ?? round($data['price_day'] * 5, 2), 'is_active' => true],
            ];
            $item->pricingTiers()->createMany($tiers);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $path = $photo->store("items/{$item->id}", 'public');
                    $item->photos()->create([
                        'photo_url' => $path,
                        'is_primary' => $index === 0,
                        'sort_order' => $index,
                    ]);
                }
            }
        });

        return redirect()->route('provider.items.index')->with('success', 'Item created successfully.');
    }

    public function edit(Request $request, Item $item): Response
    {
        $this->assertProviderOwnsItem($request, $item);

        $item->load([
            'tags:id',
            'variants',
            'pricingTiers',
            'photos:id,item_id,photo_url,is_primary,sort_order',
        ]);

        return Inertia::render('Provider/Items/Edit', [
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'category_id' => $item->category_id,
                'description' => $item->description,
                'brand' => $item->brand,
                'designer' => $item->designer,
                'condition_rating' => $item->condition_rating,
                'cleaning_policy' => $item->cleaning_policy,
                'security_deposit' => (float) $item->security_deposit,
                'late_fee_per_day' => (float) $item->late_fee_per_day,
                'is_active' => $item->is_active,
                'requires_approval' => $item->requires_approval,
                'tag_ids' => $item->tags->pluck('id')->values(),
                'variants' => $item->variants,
                'pricing_tiers' => $item->pricingTiers->map(fn (PricingTier $tier) => [
                    'duration_days' => $tier->duration_days,
                    'price' => (float) $tier->price,
                ])->values(),
                'photos' => $item->photos,
            ],
            'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $this->assertProviderOwnsItem($request, $item);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:100'],
            'designer' => ['nullable', 'string', 'max:100'],
            'condition_rating' => ['required', 'in:new,like_new,good,fair'],
            'cleaning_policy' => ['nullable', 'string'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'late_fee_per_day' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'requires_approval' => ['nullable', 'boolean'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'price_day' => ['required', 'numeric', 'min:1'],
            'price_3_days' => ['nullable', 'numeric', 'min:1'],
            'price_7_days' => ['nullable', 'numeric', 'min:1'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.size_label' => ['required_with:variants', 'string', 'max:50'],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.material' => ['nullable', 'string', 'max:100'],
            'variants.*.quantity_available' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.chest_cm' => ['nullable', 'numeric', 'min:0'],
            'variants.*.waist_cm' => ['nullable', 'numeric', 'min:0'],
            'variants.*.length_cm' => ['nullable', 'numeric', 'min:0'],
            'variants.*.inseam_cm' => ['nullable', 'numeric', 'min:0'],
            'variants.*.shoulder_cm' => ['nullable', 'numeric', 'min:0'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:5120'],
            'remove_photo_ids' => ['nullable', 'array'],
            'remove_photo_ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($item, $data, $request): void {
            $item->update([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'description' => $data['description'] ?? null,
                'brand' => $data['brand'] ?? null,
                'designer' => $data['designer'] ?? null,
                'condition_rating' => $data['condition_rating'],
                'cleaning_policy' => $data['cleaning_policy'] ?? null,
                'security_deposit' => $data['security_deposit'] ?? 0,
                'late_fee_per_day' => $data['late_fee_per_day'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
                'requires_approval' => $data['requires_approval'] ?? false,
            ]);

            $item->tags()->sync($data['tag_ids'] ?? []);

            if (! empty($data['variants'])) {
                foreach ($data['variants'] as $index => $variantData) {
                    $attributes = [
                        'size_label' => $variantData['size_label'],
                        'color' => $variantData['color'] ?? null,
                        'material' => $variantData['material'] ?? null,
                        'quantity_available' => $variantData['quantity_available'],
                        'chest_cm' => $variantData['chest_cm'] ?? null,
                        'waist_cm' => $variantData['waist_cm'] ?? null,
                        'length_cm' => $variantData['length_cm'] ?? null,
                        'inseam_cm' => $variantData['inseam_cm'] ?? null,
                        'shoulder_cm' => $variantData['shoulder_cm'] ?? null,
                        'is_active' => true,
                    ];

                    if (! empty($variantData['id'])) {
                        $variant = $item->variants()->where('id', $variantData['id'])->first();
                        if ($variant) {
                            $variant->update($attributes);
                            continue;
                        }
                    }

                    $item->variants()->create(array_merge($attributes, [
                        'sku' => strtoupper('RF-'.$item->id.'-'.($index + 1).'-'.Str::random(4)),
                    ]));
                }
            }

            $tierValues = [
                1 => $data['price_day'],
                3 => $data['price_3_days'] ?? round($data['price_day'] * 2.5, 2),
                7 => $data['price_7_days'] ?? round($data['price_day'] * 5, 2),
            ];

            foreach ($tierValues as $days => $price) {
                $item->pricingTiers()->updateOrCreate(
                    ['duration_days' => $days],
                    ['price' => $price, 'is_active' => true]
                );
            }

            if (! empty($data['remove_photo_ids'])) {
                $photos = $item->photos()->whereIn('id', $data['remove_photo_ids'])->get();
                foreach ($photos as $photo) {
                    Storage::disk('public')->delete($photo->photo_url);
                    $photo->delete();
                }
            }

            if ($request->hasFile('photos')) {
                $sortOffset = (int) $item->photos()->max('sort_order') + 1;
                foreach ($request->file('photos') as $index => $photo) {
                    $path = $photo->store("items/{$item->id}", 'public');
                    $item->photos()->create([
                        'photo_url' => $path,
                        'is_primary' => $item->photos()->doesntExist() && $index === 0,
                        'sort_order' => $sortOffset + $index,
                    ]);
                }
            }
        });

        return redirect()->route('provider.items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(Request $request, Item $item): RedirectResponse
    {
        $this->assertProviderOwnsItem($request, $item);

        foreach ($item->photos as $photo) {
            Storage::disk('public')->delete($photo->photo_url);
        }

        $item->delete();

        return redirect()->route('provider.items.index')->with('success', 'Item deleted successfully.');
    }

    private function assertProviderOwnsItem(Request $request, Item $item): void
    {
        $providerId = $request->user()?->provider?->id;
        abort_if((int) $item->provider_id !== (int) $providerId, 403, 'Unauthorized item access.');
    }
}


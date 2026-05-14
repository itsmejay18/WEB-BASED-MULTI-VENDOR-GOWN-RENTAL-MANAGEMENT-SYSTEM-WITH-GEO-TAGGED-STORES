<?php

namespace App\Http\Controllers\Web\Provider;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VariantController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'size_label' => ['required', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
            'material' => ['nullable', 'string', 'max:100'],
            'quantity_available' => ['required', 'integer', 'min:0'],
            'chest_cm' => ['nullable', 'numeric', 'min:0'],
            'waist_cm' => ['nullable', 'numeric', 'min:0'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'inseam_cm' => ['nullable', 'numeric', 'min:0'],
            'shoulder_cm' => ['nullable', 'numeric', 'min:0'],
        ]);

        $item = Item::findOrFail($data['item_id']);
        $this->assertProviderOwnsItem($request, $item);

        $item->variants()->create([
            'sku' => strtoupper('RF-'.$item->id.'-'.Str::random(8)),
            'size_label' => $data['size_label'],
            'color' => $data['color'] ?? null,
            'material' => $data['material'] ?? null,
            'quantity_available' => $data['quantity_available'],
            'chest_cm' => $data['chest_cm'] ?? null,
            'waist_cm' => $data['waist_cm'] ?? null,
            'length_cm' => $data['length_cm'] ?? null,
            'inseam_cm' => $data['inseam_cm'] ?? null,
            'shoulder_cm' => $data['shoulder_cm'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Variant added.');
    }

    public function update(Request $request, ItemVariant $variant): RedirectResponse
    {
        $variant->loadMissing('item');
        $this->assertProviderOwnsItem($request, $variant->item);

        $data = $request->validate([
            'size_label' => ['required', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
            'material' => ['nullable', 'string', 'max:100'],
            'quantity_available' => ['required', 'integer', 'min:0'],
            'chest_cm' => ['nullable', 'numeric', 'min:0'],
            'waist_cm' => ['nullable', 'numeric', 'min:0'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'inseam_cm' => ['nullable', 'numeric', 'min:0'],
            'shoulder_cm' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $variant->update([
            'size_label' => $data['size_label'],
            'color' => $data['color'] ?? null,
            'material' => $data['material'] ?? null,
            'quantity_available' => $data['quantity_available'],
            'chest_cm' => $data['chest_cm'] ?? null,
            'waist_cm' => $data['waist_cm'] ?? null,
            'length_cm' => $data['length_cm'] ?? null,
            'inseam_cm' => $data['inseam_cm'] ?? null,
            'shoulder_cm' => $data['shoulder_cm'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return back()->with('success', 'Variant updated.');
    }

    public function destroy(Request $request, ItemVariant $variant): RedirectResponse
    {
        $variant->loadMissing('item');
        $this->assertProviderOwnsItem($request, $variant->item);

        if ($variant->bookings()->exists()) {
            return back()->with('error', 'Cannot delete variant with bookings history.');
        }

        $variant->delete();

        return back()->with('success', 'Variant deleted.');
    }

    private function assertProviderOwnsItem(Request $request, Item $item): void
    {
        $providerId = $request->user()?->provider?->id;
        abort_if((int) $item->provider_id !== (int) $providerId, 403, 'Unauthorized item variant action.');
    }
}


<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'brand' => $this->brand,
            'designer' => $this->designer,
            'condition_rating' => $this->condition_rating,
            'cleaning_policy' => $this->cleaning_policy,
            'security_deposit' => (float) $this->security_deposit,
            'late_fee_per_day' => (float) $this->late_fee_per_day,
            'total_rentals' => $this->total_rentals,
            'is_active' => $this->is_active,
            'requires_approval' => $this->requires_approval,
            'lowest_price' => $this->lowest_price,
            'average_rating' => $this->average_rating,
            'provider' => new ProviderResource($this->whenLoaded('provider')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'variants' => ItemVariantResource::collection($this->whenLoaded('variants')),
            'pricing_tiers' => $this->whenLoaded('pricingTiers'),
            'photos' => $this->whenLoaded('photos'),
            'primary_photo' => $this->whenLoaded('primaryPhoto'),
            'tags' => $this->whenLoaded('tags'),
            'distance' => $this->when(isset($this->distance), fn () => round((float) $this->distance, 2)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

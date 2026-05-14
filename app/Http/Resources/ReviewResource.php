<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'user_id' => $this->user_id,
            'provider_id' => $this->provider_id,
            'item_id' => $this->item_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'item_condition_rating' => $this->item_condition_rating,
            'communication_rating' => $this->communication_rating,
            'photos' => $this->photos,
            'is_public' => $this->is_public,
            'user' => new UserResource($this->whenLoaded('user')),
            'provider' => new ProviderResource($this->whenLoaded('provider')),
            'item' => new ItemResource($this->whenLoaded('item')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

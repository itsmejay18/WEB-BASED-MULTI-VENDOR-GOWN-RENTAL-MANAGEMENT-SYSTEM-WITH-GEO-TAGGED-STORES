<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'business_name' => $this->business_name,
            'business_registration' => $this->business_registration,
            'description' => $this->description,
            'logo' => $this->logo,
            'cover_photo' => $this->cover_photo,
            'rating' => (float) $this->rating,
            'total_reviews' => $this->total_reviews,
            'verification_status' => $this->verification_status,
            'commission_rate' => (float) $this->commission_rate,
            'user' => new UserResource($this->whenLoaded('user')),
            'main_location' => new LocationResource($this->whenLoaded('mainLocation')),
            'locations' => LocationResource::collection($this->whenLoaded('locations')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

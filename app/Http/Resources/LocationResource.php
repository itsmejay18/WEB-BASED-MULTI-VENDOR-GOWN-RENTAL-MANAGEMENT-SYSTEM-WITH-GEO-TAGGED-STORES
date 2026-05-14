<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->location_name ?? null,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'service_radius_km' => $this->service_radius_km ?? null,
            'is_main' => $this->is_main ?? null,
            'is_active' => $this->is_active ?? null,
            'is_default' => $this->is_default ?? null,
            'address_type' => $this->address_type ?? null,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'user_id' => $this->user_id,
            'provider_id' => $this->provider_id,
            'variant_id' => $this->variant_id,
            'pickup_location_id' => $this->pickup_location_id,
            'delivery_address_id' => $this->delivery_address_id,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'total_days' => $this->total_days,
            'rental_price' => (float) $this->rental_price,
            'security_deposit' => (float) $this->security_deposit,
            'delivery_fee' => (float) $this->delivery_fee,
            'platform_fee' => (float) $this->platform_fee,
            'total_amount' => (float) $this->total_amount,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'special_requests' => $this->special_requests,
            'rejection_reason' => $this->rejection_reason,
            'cancelled_by' => $this->cancelled_by,
            'user' => new UserResource($this->whenLoaded('user')),
            'provider' => new ProviderResource($this->whenLoaded('provider')),
            'variant' => new ItemVariantResource($this->whenLoaded('variant')),
            'pickup_location' => new LocationResource($this->whenLoaded('pickupLocation')),
            'delivery_address' => new LocationResource($this->whenLoaded('deliveryAddress')),
            'payments' => $this->whenLoaded('payments'),
            'review' => new ReviewResource($this->whenLoaded('review')),
            'timeline' => $this->whenLoaded('timeline'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

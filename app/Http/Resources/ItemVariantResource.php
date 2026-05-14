<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'sku' => $this->sku,
            'size_label' => $this->size_label,
            'color' => $this->color,
            'material' => $this->material,
            'measurements' => [
                'chest_cm' => $this->chest_cm,
                'waist_cm' => $this->waist_cm,
                'length_cm' => $this->length_cm,
                'inseam_cm' => $this->inseam_cm,
                'shoulder_cm' => $this->shoulder_cm,
            ],
            'quantity_available' => $this->quantity_available,
            'additional_notes' => $this->additional_notes,
            'is_active' => $this->is_active,
            'photos' => $this->whenLoaded('photos'),
        ];
    }
}

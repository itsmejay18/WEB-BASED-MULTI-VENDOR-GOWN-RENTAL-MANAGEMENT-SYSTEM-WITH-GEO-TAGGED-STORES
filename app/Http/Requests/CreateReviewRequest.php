<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'item_id' => ['nullable', 'integer', 'exists:items,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string'],
            'item_condition_rating' => ['nullable', 'integer', 'between:1,5'],
            'communication_rating' => ['nullable', 'integer', 'between:1,5'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['string', 'url', 'max:2048'],
            'is_public' => ['nullable', 'boolean'],
        ];
    }
}

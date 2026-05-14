<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'integer', 'exists:item_variants,id'],
            'pickup_location_id' => ['nullable', 'integer', 'exists:provider_locations,id'],
            'delivery_address_id' => ['nullable', 'integer', 'exists:user_addresses,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'special_requests' => ['nullable', 'string'],
            'discount_code' => ['nullable', 'string', 'max:50'],
            'status' => ['sometimes', Rule::in(['pending'])],
        ];
    }
}

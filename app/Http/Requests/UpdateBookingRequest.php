<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                Rule::in([
                    'pending',
                    'approved',
                    'rejected',
                    'cancelled',
                    'ready_for_pickup',
                    'picked_up',
                    'returned',
                    'completed',
                    'disputed',
                ]),
            ],
            'payment_status' => ['sometimes', Rule::in(['pending', 'paid', 'refunded', 'partially_refunded'])],
            'rejection_reason' => ['nullable', 'string'],
            'special_requests' => ['nullable', 'string'],
            'cancelled_by' => ['nullable', Rule::in(['user', 'provider', 'system'])],
        ];
    }
}

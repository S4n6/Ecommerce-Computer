<?php

namespace App\Containers\AppSection\Order\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateOrderStatusRequest
 *
 * Validates the body for PUT /api/v1/admin/orders/{id}/status.
 * Admin-only endpoint (enforced at the route level).
 */
class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['pending', 'processing', 'completed', 'cancelled']),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'An order status is required.',
            'status.in' => 'Status must be one of: pending, processing, completed, cancelled.',
        ];
    }
}

<?php

namespace App\Containers\AppSection\Order\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PlaceOrderRequest
 *
 * Validates the body for POST /api/v1/orders.
 * Authenticated customers use this endpoint.
 *
 * Expected body:
 * {
 *   "items": [
 *     { "product_id": 1, "quantity": 2 },
 *     { "product_id": 5, "quantity": 1 }
 *   ]
 * }
 */
class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Must be authenticated (enforced via route middleware auth:api)
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least one order item is required.',
            'items.array' => 'Items must be an array.',
            'items.min' => 'At least one item is required.',
            'items.*.product_id.required' => 'Each item must have a product_id.',
            'items.*.product_id.exists' => 'One or more products do not exist.',
            'items.*.quantity.required' => 'Each item must have a quantity.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
        ];
    }
}

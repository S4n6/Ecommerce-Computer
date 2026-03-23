<?php

namespace App\Containers\AppSection\Product\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CreateProductRequest
 *
 * Validates the body for POST /api/v1/admin/products.
 * Only authenticated Admins reach this endpoint (enforced via route middleware),
 * so authorize() returns true — authorisation is handled at the route level.
 */
class CreateProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:products,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'A product name is required.',
            'name.unique' => 'A product with this name already exists.',
            'price.required' => 'A product price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'category_id.required' => 'A category is required.',
            'category_id.exists' => 'The selected category does not exist.',
        ];
    }
}

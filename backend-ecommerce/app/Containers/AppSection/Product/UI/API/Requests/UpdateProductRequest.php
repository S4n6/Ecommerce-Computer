<?php

namespace App\Containers\AppSection\Product\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateProductRequest
 *
 * Validates the body for PUT /api/v1/admin/products/{id}.
 * All fields are optional (PATCH-style), allowing partial updates.
 */
class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('products', 'name')->ignore($productId)],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A product with this name already exists.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'category_id.exists' => 'The selected category does not exist.',
        ];
    }
}

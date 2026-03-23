<?php

namespace App\Containers\AppSection\Product\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * GetCompatibleProductsRequest
 *
 * Validates the inputs required to find compatible products for a given source product.
 *
 * Expected query parameters (GET /api/v1/products/compatible):
 *   - product_id            : int,    required — the source product (e.g. a Mainboard)
 *   - target_category_slug  : string, required — the category to filter results (e.g. 'cpu')
 */
class GetCompatibleProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Open to all for now; add Gate/Policy checks here later.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'target_category_slug' => ['required', 'string', 'exists:categories,slug'],
        ];
    }

    /**
     * Human-readable error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'A source product ID is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'target_category_slug.required' => 'A target category slug is required.',
            'target_category_slug.exists' => 'The target category was not found.',
        ];
    }
}

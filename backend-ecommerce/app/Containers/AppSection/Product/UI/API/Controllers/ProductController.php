<?php

namespace App\Containers\AppSection\Product\UI\API\Controllers;

use App\Containers\AppSection\Product\Actions\GetCompatibleProductsAction;
use App\Containers\AppSection\Product\Models\Product;
use App\Containers\AppSection\Product\UI\API\Requests\GetCompatibleProductsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ProductController
 *
 * Porto UI/API Controller — thin layer responsible only for:
 *   1. Receiving HTTP input (via FormRequest)
 *   2. Delegating work to an Action
 *   3. Returning an HTTP response
 *
 * No business logic lives here.
 */
class ProductController
{
    // ----------------------------------------------------------------
    // GET /api/v1/products
    // ----------------------------------------------------------------

    /**
     * Return products for public catalog browsing.
     *
     * Optional query params:
     *   - category: category slug, name, or id (e.g. mainboard)
     */
    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');

        $query = Product::query()->with([
            'category',
            'attributeValues.attribute',
        ]);

        if (! empty($category)) {
            $query->whereHas('category', function ($categoryQuery) use ($category) {
                $categoryQuery
                    ->where('slug', $category)
                    ->orWhere('name', $category)
                    ->orWhere('id', is_numeric($category) ? (int) $category : 0);
            });
        }

        $products = $query->get();

        return response()->json([
            'data' => $products->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'stock' => $product->stock,
                'category' => [
                    'id' => $product->category?->id,
                    'name' => $product->category?->name,
                    'slug' => $product->category?->slug,
                ],
                'attribute_values' => $product->attributeValues->map(fn ($av) => [
                    'attribute' => [
                        'id' => $av->attribute?->id,
                        'name' => $av->attribute?->name,
                        'code' => $av->attribute?->code,
                    ],
                    'value' => $av->value,
                ]),
            ]),
            'meta' => [
                'category' => $category,
                'total' => $products->count(),
            ],
        ]);
    }

    // ----------------------------------------------------------------
    // GET /api/v1/products/compatible
    // ----------------------------------------------------------------

    /**
     * Return a list of products compatible with the given source product.
     *
     * Query Parameters:
     *   - product_id            : int    (required) — e.g. the Mainboard's id
     *   - target_category_slug  : string (required) — e.g. 'cpu'
     *
     * Example:
     *   GET /api/v1/products/compatible?product_id=1&target_category_slug=cpu
     *
     * Response 200:
     * {
     *   "data": [
     *     {
     *       "id": 2,
     *       "name": "Intel Core i9-13900K",
     *       "slug": "intel-core-i9-13900k",
     *       "price": "8750000.00",
     *       "stock": 25,
     *       "attribute_values": [
     *         { "attribute": { "code": "socket_type" }, "value": "LGA1700" }
     *       ]
     *     }
     *   ],
     *   "meta": {
     *     "source_product_id": 1,
     *     "target_category_slug": "cpu",
     *     "total": 1
     *   }
     * }
     *
     * Response 422 (validation failure):
     * { "message": "...", "errors": { "product_id": [...] } }
     */
    public function getCompatibleProducts(
        GetCompatibleProductsRequest $request,
        GetCompatibleProductsAction $action,
    ): JsonResponse {
        $compatibleProducts = $action->run($request);

        return response()->json([
            'data' => $compatibleProducts->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'stock' => $product->stock,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ],
                'attribute_values' => $product->attributeValues->map(fn ($av) => [
                    'attribute' => [
                        'id' => $av->attribute->id,
                        'name' => $av->attribute->name,
                        'code' => $av->attribute->code,
                    ],
                    'value' => $av->value,
                ]),
            ]),
            'meta' => [
                'source_product_id' => (int) $request->validated('product_id'),
                'target_category_slug' => $request->validated('target_category_slug'),
                'total' => $compatibleProducts->count(),
            ],
        ]);
    }
}

<?php

namespace App\Containers\AppSection\Product\UI\API\Controllers;

use App\Containers\AppSection\Product\Actions\CreateProductAction;
use App\Containers\AppSection\Product\Actions\DeleteProductAction;
use App\Containers\AppSection\Product\Actions\ListProductsAction;
use App\Containers\AppSection\Product\Actions\UpdateProductAction;
use App\Containers\AppSection\Product\UI\API\Requests\CreateProductRequest;
use App\Containers\AppSection\Product\UI\API\Requests\UpdateProductRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AdminProductController
 *
 * Porto UI/API Controller — Admin-only CRUD operations for Products.
 * All routes pointing here are protected by the 'role:Admin' middleware
 * defined in api.php, so no additional auth logic is needed here.
 *
 * Routes handled:
 *   GET    /api/v1/admin/products            → index()
 *   POST   /api/v1/admin/products            → store()
 *   PUT    /api/v1/admin/products/{id}       → update()
 *   DELETE /api/v1/admin/products/{id}       → destroy()
 */
class AdminProductController extends Controller
{
    // ----------------------------------------------------------------
    // GET /api/v1/admin/products
    // ----------------------------------------------------------------

    /**
     * List all products (paginated).
     *
     * Query params (powered by l5-repository RequestCriteria):
     *   page, per_page, search, searchFields, orderBy, sortedBy
     *
     * Response 200:
     * { "data": [...], "links": {...}, "meta": { "current_page": 1, "total": 50, ... } }
     */
    public function index(Request $request, ListProductsAction $action): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $products = $action->run($perPage);

        return response()->json($products);
    }

    // ----------------------------------------------------------------
    // POST /api/v1/admin/products
    // ----------------------------------------------------------------

    /**
     * Create a new product.
     *
     * Body: { "name": "...", "price": 999.00, "category_id": 1, "stock": 100 }
     *
     * Response 201: { "message": "...", "data": { product } }
     */
    public function store(CreateProductRequest $request, CreateProductAction $action): JsonResponse
    {
        $product = $action->run($request);

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product->load('category'),
        ], 201);
    }

    // ----------------------------------------------------------------
    // PUT /api/v1/admin/products/{id}
    // ----------------------------------------------------------------

    /**
     * Update an existing product.
     *
     * Body: { "name"?: "...", "price"?: 999.00, "category_id"?: 1, "stock"?: 100 }
     *
     * Response 200: { "message": "...", "data": { product } }
     */
    public function update(UpdateProductRequest $request, UpdateProductAction $action, int $id): JsonResponse
    {
        $product = $action->run($request, $id);

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product->load('category'),
        ]);
    }

    // ----------------------------------------------------------------
    // DELETE /api/v1/admin/products/{id}
    // ----------------------------------------------------------------

    /**
     * Delete a product.
     *
     * Response 200: { "message": "Product deleted successfully." }
     */
    public function destroy(DeleteProductAction $action, int $id): JsonResponse
    {
        $action->run($id);

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }
}

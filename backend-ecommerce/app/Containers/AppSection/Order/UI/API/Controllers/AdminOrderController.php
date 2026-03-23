<?php

namespace App\Containers\AppSection\Order\UI\API\Controllers;

use App\Containers\AppSection\Order\Actions\ListOrdersAction;
use App\Containers\AppSection\Order\Actions\UpdateOrderStatusAction;
use App\Containers\AppSection\Order\UI\API\Requests\UpdateOrderStatusRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AdminOrderController
 *
 * Porto UI/API Controller — Admin-only Order management endpoints.
 * All routes here are guarded by 'role:Admin' middleware in api.php.
 *
 * Routes handled:
 *   GET  /api/v1/admin/orders              → index()
 *   PUT  /api/v1/admin/orders/{id}/status  → updateStatus()
 */
class AdminOrderController extends Controller
{
    // ----------------------------------------------------------------
    // GET /api/v1/admin/orders
    // ----------------------------------------------------------------

    /**
     * List all orders (paginated), with customer info and items.
     *
     * Query params: page, per_page
     *
     * Response 200: paginated LengthAwarePaginator JSON
     */
    public function index(Request $request, ListOrdersAction $action): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $orders = $action->run($perPage);

        return response()->json($orders);
    }

    // ----------------------------------------------------------------
    // PUT /api/v1/admin/orders/{id}/status
    // ----------------------------------------------------------------

    /**
     * Update the status of a specific order.
     *
     * Body: { "status": "processing" }
     *
     * Response 200:
     * { "message": "Order status updated.", "data": { order } }
     */
    public function updateStatus(
        UpdateOrderStatusRequest $request,
        UpdateOrderStatusAction $action,
        int $id,
    ): JsonResponse {
        $order = $action->run($request, $id);

        return response()->json([
            'message' => 'Order status updated successfully.',
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
            ],
        ]);
    }
}

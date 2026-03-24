<?php

namespace App\Containers\AppSection\Order\UI\API\Controllers;

use App\Containers\AppSection\Order\Actions\CancelMyOrderAction;
use App\Containers\AppSection\Order\Actions\GetMyOrdersAction;
use App\Containers\AppSection\Order\Actions\PlaceOrderAction;
use App\Containers\AppSection\Order\Models\Order;
use App\Containers\AppSection\Order\UI\API\Requests\PlaceOrderRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * OrderController
 *
 * Porto UI/API Controller — Customer-facing Order endpoints.
 *
 * Routes handled:
 *   POST /api/v1/orders   → placeOrder()  (auth:api required)
 */
class OrderController extends Controller
{
    // ----------------------------------------------------------------
    // POST /api/v1/orders
    // ----------------------------------------------------------------

    /**
     * Place a new order for the authenticated customer.
     *
     * Body:
     * {
     *   "items": [
     *     { "product_id": 1, "quantity": 2 },
     *     { "product_id": 5, "quantity": 1 }
     *   ]
     * }
     *
     * Response 201:
     * {
     *   "message": "Order placed successfully.",
     *   "data": {
     *     "id": 10,
     *     "total_price": "29750000.00",
     *     "status": "pending",
     *     "items": [...]
     *   }
     * }
     */
    public function placeOrder(PlaceOrderRequest $request, PlaceOrderAction $action): JsonResponse
    {
        $order = $action->run($request);

        return response()->json([
            'message' => 'Order placed successfully.',
            'data' => [
                'id' => $order->id,
                'total_price' => $order->total_price,
                'status' => $order->status,
                'items' => $order->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'line_total' => round($item->price * $item->quantity, 2),
                ]),
            ],
        ], 201);
    }

    // ----------------------------------------------------------------
    // GET /api/v1/my-orders
    // ----------------------------------------------------------------

    /**
     * Get all orders for the authenticated customer.
     *
     * Returns orders ordered by latest first, with items and products eager loaded.
     */
    public function myOrders(GetMyOrdersAction $action): JsonResponse
    {
        $orders = $action->run((int) auth()->id());

        return response()->json([
            'data' => $orders->map(fn (Order $order) => $this->transformOrder($order)),
        ]);
    }

    // ----------------------------------------------------------------
    // PATCH /api/v1/my-orders/{id}/cancel
    // ----------------------------------------------------------------

    /**
     * Cancel a pending order belonging to the authenticated customer.
     */
    public function cancelMyOrder(int $id, CancelMyOrderAction $action): JsonResponse
    {
        $order = $action->run(orderId: $id, userId: (int) auth()->id());

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data' => $this->transformOrder($order),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function transformOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'total_price' => $order->total_price,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'items' => $order->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->price,
                'line_total' => round($item->price * $item->quantity, 2),
            ]),
        ];
    }
}

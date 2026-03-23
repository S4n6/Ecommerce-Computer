<?php

namespace App\Containers\AppSection\Order\UI\API\Controllers;

use App\Containers\AppSection\Order\Actions\PlaceOrderAction;
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
}

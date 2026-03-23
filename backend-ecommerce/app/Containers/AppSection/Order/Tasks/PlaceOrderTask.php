<?php

namespace App\Containers\AppSection\Order\Tasks;

use App\Containers\AppSection\Order\Models\Order;
use App\Containers\AppSection\Order\Models\OrderItem;
use App\Containers\AppSection\Order\Repositories\OrderRepository;
use App\Containers\AppSection\Product\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * PlaceOrderTask
 *
 * Creates an order for a customer based on the provided line items.
 *
 * Notes:
 * - Uses a database transaction to avoid partial writes.
 * - Snapshots product price into the order items at time of purchase.
 */
class PlaceOrderTask
{
    public function __construct(
        private readonly OrderRepository $repository,
    ) {}

    /**
     * @param  int  $userId  The authenticated customer's user ID
     * @param  array  $items  Array of ['product_id' => int, 'quantity' => int]
     * @return Order The newly created Order with its items loaded
     *
     * @throws ValidationException If the payload references a missing product
     */
    public function run(int $userId, array $items): Order
    {
        return DB::transaction(function () use ($userId, $items) {
            // 1. Fetch all products in a single query for efficiency.
            $productIds = array_column($items, 'product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            // 2. Build OrderItem data & calculate total price simultaneously.
            $totalPrice = 0;
            $orderItemData = [];

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];

                /** @var Product $product */
                $product = $products->get($productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => ["Product ID {$productId} not found."],
                    ]);
                }

                $unitPrice = (float) $product->price;
                $lineTotal = $unitPrice * $quantity;
                $totalPrice += $lineTotal;

                $orderItemData[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $unitPrice, // price snapshot
                ];
            }

            // 3. Create the Order header record.
            /** @var Order $order */
            $order = $this->repository->create([
                'user_id' => $userId,
                'total_price' => round($totalPrice, 2),
                'status' => 'pending',
            ]);

            // 4. Attach user_id / order_id to each item row and bulk-insert.
            foreach ($orderItemData as &$row) {
                $row['order_id'] = $order->id;
                $row['created_at'] = now();
                $row['updated_at'] = now();
            }
            unset($row);

            OrderItem::insert($orderItemData);

            // 5. Return the order with its items and product info eager-loaded.
            return $order->load('items.product');
        });
    }
}

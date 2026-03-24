<?php

namespace App\Containers\AppSection\Order\Tasks;

use App\Containers\AppSection\Order\Models\Order;
use App\Containers\AppSection\Order\Repositories\OrderRepository;
use Illuminate\Support\Collection;

/**
 * GetMyOrdersTask
 *
 * Fetches all orders for a specific customer (latest first), including
 * order items and product details.
 */
class GetMyOrdersTask
{
    public function __construct(
        private readonly OrderRepository $repository,
    ) {}

    /**
     * @return Collection<int, Order>
     */
    public function run(int $userId): Collection
    {
        return Order::query()
            ->with(['items.product'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }
}

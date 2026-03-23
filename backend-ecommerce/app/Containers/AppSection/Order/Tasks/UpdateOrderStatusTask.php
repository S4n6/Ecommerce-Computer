<?php

namespace App\Containers\AppSection\Order\Tasks;

use App\Containers\AppSection\Order\Models\Order;
use App\Containers\AppSection\Order\Repositories\OrderRepository;

/**
 * UpdateOrderStatusTask
 *
 * Updates the status column of a single Order record.
 * Allowed values: pending | processing | completed | cancelled
 * (Enforced at the Request layer, not here.)
 */
class UpdateOrderStatusTask
{
    public function __construct(
        private readonly OrderRepository $repository,
    ) {}

    public function run(int $orderId, string $status): Order
    {
        /** @var Order $order */
        $order = $this->repository->update(['status' => $status], $orderId);

        return $order;
    }
}

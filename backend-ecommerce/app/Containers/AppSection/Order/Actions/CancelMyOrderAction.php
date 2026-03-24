<?php

namespace App\Containers\AppSection\Order\Actions;

use App\Containers\AppSection\Order\Models\Order;
use App\Containers\AppSection\Order\Tasks\CancelMyOrderTask;

/**
 * CancelMyOrderAction
 *
 * Porto Action — orchestrates the "Cancel My Order" customer use-case.
 */
class CancelMyOrderAction
{
    public function __construct(
        private readonly CancelMyOrderTask $task,
    ) {}

    public function run(int $orderId, int $userId): Order
    {
        return $this->task->run(orderId: $orderId, userId: $userId);
    }
}

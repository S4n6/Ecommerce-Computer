<?php

namespace App\Containers\AppSection\Order\Actions;

use App\Containers\AppSection\Order\Models\Order;
use App\Containers\AppSection\Order\Tasks\UpdateOrderStatusTask;
use App\Containers\AppSection\Order\UI\API\Requests\UpdateOrderStatusRequest;

/**
 * UpdateOrderStatusAction
 *
 * Porto Action — orchestrates the "Update Order Status" admin use-case.
 */
class UpdateOrderStatusAction
{
    public function __construct(
        private readonly UpdateOrderStatusTask $task,
    ) {}

    public function run(UpdateOrderStatusRequest $request, int $orderId): Order
    {
        return $this->task->run(
            orderId: $orderId,
            status: $request->validated('status'),
        );
    }
}

<?php

namespace App\Containers\AppSection\Order\Actions;

use App\Containers\AppSection\Order\Models\Order;
use App\Containers\AppSection\Order\Tasks\PlaceOrderTask;
use App\Containers\AppSection\Order\UI\API\Requests\PlaceOrderRequest;

/**
 * PlaceOrderAction
 *
 * Porto Action — orchestrates the "Place Order" use-case.
 *
 * Receives the validated FormRequest, extracts the authenticated user's ID,
 * and delegates the full order-creation logic to PlaceOrderTask.
 */
class PlaceOrderAction
{
    public function __construct(
        private readonly PlaceOrderTask $task,
    ) {}

    public function run(PlaceOrderRequest $request): Order
    {
        return $this->task->run(
            userId: (int) $request->user()->id,
            items: $request->validated('items'),
        );
    }
}

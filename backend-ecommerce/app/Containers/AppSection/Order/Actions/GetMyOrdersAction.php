<?php

namespace App\Containers\AppSection\Order\Actions;

use App\Containers\AppSection\Order\Models\Order;
use App\Containers\AppSection\Order\Tasks\GetMyOrdersTask;
use Illuminate\Support\Collection;

/**
 * GetMyOrdersAction
 *
 * Porto Action — orchestrates the "Get My Orders" customer use-case.
 */
class GetMyOrdersAction
{
    public function __construct(
        private readonly GetMyOrdersTask $task,
    ) {}

    /**
     * @return Collection<int, Order>
     */
    public function run(int $userId): Collection
    {
        return $this->task->run($userId);
    }
}

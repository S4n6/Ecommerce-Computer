<?php

namespace App\Containers\AppSection\Order\Actions;

use App\Containers\AppSection\Order\Tasks\ListOrdersTask;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * ListOrdersAction
 *
 * Porto Action — orchestrates the "List All Orders" admin use-case.
 */
class ListOrdersAction
{
    public function __construct(
        private readonly ListOrdersTask $task,
    ) {}

    public function run(int $perPage = 15): LengthAwarePaginator
    {
        return $this->task->run($perPage);
    }
}

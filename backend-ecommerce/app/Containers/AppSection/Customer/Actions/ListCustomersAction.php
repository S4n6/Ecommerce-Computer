<?php

namespace App\Containers\AppSection\Customer\Actions;

use App\Containers\AppSection\Customer\Tasks\ListCustomersTask;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * ListCustomersAction
 *
 * Porto Action — orchestrates the "List Customers" admin use-case.
 */
class ListCustomersAction
{
    public function __construct(
        private readonly ListCustomersTask $task,
    ) {}

    public function run(int $perPage = 15): LengthAwarePaginator
    {
        return $this->task->run($perPage);
    }
}

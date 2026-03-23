<?php

namespace App\Containers\AppSection\Product\Actions;

use App\Containers\AppSection\Product\Tasks\ListProductsTask;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * ListProductsAction
 *
 * Porto Action — orchestrates the "List Products" use-case.
 */
class ListProductsAction
{
    public function __construct(
        private readonly ListProductsTask $task,
    ) {}

    public function run(int $perPage = 15): LengthAwarePaginator
    {
        return $this->task->run($perPage);
    }
}

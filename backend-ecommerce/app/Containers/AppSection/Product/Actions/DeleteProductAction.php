<?php

namespace App\Containers\AppSection\Product\Actions;

use App\Containers\AppSection\Product\Tasks\DeleteProductTask;

/**
 * DeleteProductAction
 *
 * Porto Action — orchestrates the "Delete Product" use-case.
 */
class DeleteProductAction
{
    public function __construct(
        private readonly DeleteProductTask $task,
    ) {}

    public function run(int $productId): bool
    {
        return $this->task->run($productId);
    }
}

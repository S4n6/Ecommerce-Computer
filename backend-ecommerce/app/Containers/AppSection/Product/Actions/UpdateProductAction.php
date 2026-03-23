<?php

namespace App\Containers\AppSection\Product\Actions;

use App\Containers\AppSection\Product\Models\Product;
use App\Containers\AppSection\Product\Tasks\UpdateProductTask;
use App\Containers\AppSection\Product\UI\API\Requests\UpdateProductRequest;

/**
 * UpdateProductAction
 *
 * Porto Action — orchestrates the "Update Product" use-case.
 */
class UpdateProductAction
{
    public function __construct(
        private readonly UpdateProductTask $task,
    ) {}

    public function run(UpdateProductRequest $request, int $productId): Product
    {
        return $this->task->run($productId, $request->validated());
    }
}

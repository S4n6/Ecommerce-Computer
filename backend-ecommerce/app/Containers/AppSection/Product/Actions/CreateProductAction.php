<?php

namespace App\Containers\AppSection\Product\Actions;

use App\Containers\AppSection\Product\Models\Product;
use App\Containers\AppSection\Product\Tasks\CreateProductTask;
use App\Containers\AppSection\Product\UI\API\Requests\CreateProductRequest;

/**
 * CreateProductAction
 *
 * Porto Action — orchestrates the "Create Product" use-case.
 * Accepts the validated FormRequest, delegates persistence to
 * CreateProductTask, and returns the newly created Product model.
 */
class CreateProductAction
{
    public function __construct(
        private readonly CreateProductTask $task,
    ) {}

    public function run(CreateProductRequest $request): Product
    {
        return $this->task->run($request->validated());
    }
}

<?php

namespace App\Containers\AppSection\Product\Actions;

use App\Containers\AppSection\Product\Tasks\GetCompatibleProductsTask;
use App\Containers\AppSection\Product\UI\API\Requests\GetCompatibleProductsRequest;
use Illuminate\Database\Eloquent\Collection;

/**
 * GetCompatibleProductsAction
 *
 * Porto Action — orchestrates the "Get Compatible Products" use case.
 *
 * Responsibilities:
 *   - Accept the validated FormRequest
 *   - Delegate the actual query work to GetCompatibleProductsTask
 *   - Return the result to the Controller
 *
 * Porto rule: One Action per use-case. Actions may call multiple Tasks
 * but never call other Actions.
 */
class GetCompatibleProductsAction
{
    public function __construct(
        private readonly GetCompatibleProductsTask $task,
    ) {}

    /**
     * Run the action.
     */
    public function run(GetCompatibleProductsRequest $request): Collection
    {
        return $this->task->run(
            sourceProductId: (int) $request->validated('product_id'),
            targetCategorySlug: (string) $request->validated('target_category_slug'),
        );
    }
}

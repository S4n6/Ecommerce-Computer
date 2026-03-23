<?php

namespace App\Containers\AppSection\Order\Tasks;

use App\Containers\AppSection\Order\Repositories\OrderRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * ListOrdersTask
 *
 * Returns a paginated list of all orders (Admin view).
 * Eager-loads user and items.product so the response is fully hydrated.
 */
class ListOrdersTask
{
    public function __construct(
        private readonly OrderRepository $repository,
    ) {}

    public function run(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository
            ->with(['user', 'items.product'])
            ->paginate($perPage);
    }
}

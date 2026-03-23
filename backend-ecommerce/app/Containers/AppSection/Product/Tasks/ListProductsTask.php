<?php

namespace App\Containers\AppSection\Product\Tasks;

use App\Containers\AppSection\Product\Repositories\ProductRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * ListProductsTask
 *
 * Returns a paginated list of all products from the database.
 * Supports l5-repository's RequestCriteria — consumers may append
 * ?search=keyword&searchFields=name:like&orderBy=price&sortedBy=asc
 * to the request to filter/sort dynamically.
 */
class ListProductsTask
{
    public function __construct(
        private readonly ProductRepository $repository,
    ) {}

    /**
     * @param  int  $perPage  Items per page (default 15)
     *
     * @throws RepositoryException
     */
    public function run(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->with(['category'])->paginate($perPage);
    }
}

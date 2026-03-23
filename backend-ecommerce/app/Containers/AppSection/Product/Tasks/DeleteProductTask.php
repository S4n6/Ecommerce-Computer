<?php

namespace App\Containers\AppSection\Product\Tasks;

use App\Containers\AppSection\Product\Repositories\ProductRepository;

/**
 * DeleteProductTask
 *
 * Deletes a product record from the database via the ProductRepository.
 * Returns true on success so the caller can confirm the deletion.
 */
class DeleteProductTask
{
    public function __construct(
        private readonly ProductRepository $repository,
    ) {}

    public function run(int $productId): bool
    {
        return (bool) $this->repository->delete($productId);
    }
}

<?php

namespace App\Containers\AppSection\Product\Tasks;

use App\Containers\AppSection\Product\Models\Product;
use App\Containers\AppSection\Product\Repositories\ProductRepository;
use Illuminate\Support\Str;

/**
 * CreateProductTask
 *
 * Persists a new product record to the database using the ProductRepository.
 * Auto-generates a URL-friendly slug from the product name if none is provided.
 */
class CreateProductTask
{
    public function __construct(
        private readonly ProductRepository $repository,
    ) {}

    /**
     * @param  array{name: string, price: float|int, category_id: int, slug?: string, stock?: int}  $data
     */
    public function run(array $data): Product
    {
        // Auto-generate slug from name if not explicitly provided.
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        /** @var Product $product */
        $product = $this->repository->create($data);

        return $product;
    }
}

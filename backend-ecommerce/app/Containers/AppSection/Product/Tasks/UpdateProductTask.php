<?php

namespace App\Containers\AppSection\Product\Tasks;

use App\Containers\AppSection\Product\Models\Product;
use App\Containers\AppSection\Product\Repositories\ProductRepository;
use Illuminate\Support\Str;

/**
 * UpdateProductTask
 *
 * Updates an existing product record via the ProductRepository.
 * If the name changes and no explicit slug is provided, the slug is regenerated.
 */
class UpdateProductTask
{
    public function __construct(
        private readonly ProductRepository $repository,
    ) {}

    /**
     * @param  array  $data  Partial or full set of fillable fields
     */
    public function run(int $productId, array $data): Product
    {
        // Regenerate slug when name changes and caller didn't supply one.
        if (! empty($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        /** @var Product $product */
        $product = $this->repository->update($data, $productId);

        return $product;
    }
}

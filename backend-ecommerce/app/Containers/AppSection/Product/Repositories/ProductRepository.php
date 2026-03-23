<?php

namespace App\Containers\AppSection\Product\Repositories;

use Prettus\Repository\Contracts\Repository as RepositoryInterface;

/**
 * Interface ProductRepository
 *
 * Contract for the Product repository following the l5-repository pattern.
 * Extend this interface to add custom query method signatures as the
 * application grows (e.g., findByCategory, searchByAttribute).
 */
interface ProductRepository extends RepositoryInterface {}

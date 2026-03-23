<?php

namespace App\Containers\AppSection\Category\Repositories;

use Prettus\Repository\Contracts\Repository as RepositoryInterface;

/**
 * Interface CategoryRepository
 *
 * Contract for the Category repository following the l5-repository pattern.
 * Extend this interface to add custom query method signatures as the
 * application grows (e.g., getRootCategories, getTreeStructure).
 */
interface CategoryRepository extends RepositoryInterface {}

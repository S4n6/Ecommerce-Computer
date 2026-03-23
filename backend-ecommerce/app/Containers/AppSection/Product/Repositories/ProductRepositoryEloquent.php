<?php

namespace App\Containers\AppSection\Product\Repositories;

use App\Containers\AppSection\Product\Models\Product;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\Repository as BaseRepository;

/**
 * Class ProductRepositoryEloquent
 *
 * Eloquent implementation of the ProductRepository interface.
 * Uses l5-repository's BaseRepository which provides out-of-the-box:
 *  - CRUD operations (create, update, delete, find, findWhere, etc.)
 *  - Criteria-based filtering
 *  - Cache-able queries (via CacheableRepository trait if needed)
 *  - Presenter / Transformer integration
 */
class ProductRepositoryEloquent extends BaseRepository implements ProductRepository
{
    public function __construct()
    {
        parent::__construct(app($this->model()));
    }

    /**
     * Specify the Eloquent Model class name.
     */
    public function model(): string
    {
        return Product::class;
    }

    /**
     * Searchable fields for RequestCriteria.
     *
     * These fields can be used as query parameters
     * (e.g., ?search=keyword&searchFields=name:like).
     *
     * @var array<string, string>
     */
    protected $fieldSearchable = [
        'name' => 'like',
        'slug' => '=',
        'category_id' => '=',
        'price' => '=',
    ];

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot(): void
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}

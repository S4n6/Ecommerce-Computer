<?php

namespace App\Containers\AppSection\Order\Repositories;

use App\Containers\AppSection\Order\Models\Order;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\Repository as BaseRepository;

/**
 * Class OrderRepositoryEloquent
 *
 * Eloquent implementation of OrderRepository.
 * Inherits full CRUD + criteria support from l5-repository's BaseRepository.
 */
class OrderRepositoryEloquent extends BaseRepository implements OrderRepository
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
        return Order::class;
    }

    /**
     * Searchable fields for RequestCriteria.
     *
     * @var array<string, string>
     */
    protected $fieldSearchable = [
        'user_id' => '=',
        'status' => '=',
    ];

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot(): void
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}

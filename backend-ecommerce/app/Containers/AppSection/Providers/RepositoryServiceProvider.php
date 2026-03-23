<?php

namespace App\Containers\AppSection\Providers;

use App\Containers\AppSection\Category\Repositories\CategoryRepository;
use App\Containers\AppSection\Category\Repositories\CategoryRepositoryEloquent;
use App\Containers\AppSection\Order\Repositories\OrderRepository;
use App\Containers\AppSection\Order\Repositories\OrderRepositoryEloquent;
use App\Containers\AppSection\Product\Repositories\ProductRepository;
use App\Containers\AppSection\Product\Repositories\ProductRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

/**
 * RepositoryServiceProvider
 *
 * Binds each Repository Interface to its Eloquent implementation
 * so that Laravel's IoC container can resolve repository dependencies
 * automatically via constructor injection.
 *
 * Register this provider in bootstrap/providers.php (Laravel 11+).
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings.
     */
    public function register(): void
    {
        $this->app->bind(
            ProductRepository::class,
            ProductRepositoryEloquent::class
        );

        $this->app->bind(
            CategoryRepository::class,
            CategoryRepositoryEloquent::class
        );

        $this->app->bind(
            OrderRepository::class,
            OrderRepositoryEloquent::class
        );
    }
}

<?php

use App\Containers\AppSection\Authorization\UI\API\Controllers\AuthController;
use App\Containers\AppSection\Customer\UI\API\Controllers\AdminCustomerController;
use App\Containers\AppSection\Order\UI\API\Controllers\AdminOrderController;
use App\Containers\AppSection\Order\UI\API\Controllers\OrderController;
use App\Containers\AppSection\Product\UI\API\Controllers\AdminProductController;
use App\Containers\AppSection\Product\UI\API\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes here are automatically prefixed with /api and receive the
| "api" middleware group (throttle:api, bindings).
|
| Version prefix: /api/v1/...
|
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Auth Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {

        /**
         * Login
         *
         * Validates credentials and returns a Passport Bearer token.
         *
         * POST /api/v1/auth/login
         * Body: { "email": "...", "password": "..." }
         */
        Route::post('/login', [AuthController::class, 'login'])
            ->name('api.v1.auth.login');

        /**
         * Get Authenticated User
         *
         * Returns the currently authenticated user with their roles.
         * Requires: Authorization: Bearer <token>
         *
         * GET /api/v1/auth/me
         */
        Route::middleware('auth:api')->get('/me', function () {
            $user = auth()->user();

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ]);
        })->name('api.v1.auth.me');
    });

    /*
    |--------------------------------------------------------------------------
    | Product Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('products')->group(function () {

        /**
         * List Products
         *
         * Public catalog listing endpoint.
         * Optional query param:
         *   category : string (category slug, name, or id)
         *
         * GET /api/v1/products
         * Example:
         *   GET /api/v1/products?category=mainboard
         */
        Route::get('/', [ProductController::class, 'index'])
            ->name('api.v1.products.index');

        /**
         * Get Compatible Products
         *
         * Finds products in a target category that are compatible with
         * the source product based on shared EAV attribute values.
         *
         * GET /api/v1/products/compatible
         *
         * Query Params:
         *   product_id            : int    (required)
         *   target_category_slug  : string (required)
         *
         * Example:
         *   GET /api/v1/products/compatible?product_id=1&target_category_slug=cpu
         */
        Route::get('/compatible', [ProductController::class, 'getCompatibleProducts'])
            ->name('api.v1.products.compatible');
    });

    /*
    |--------------------------------------------------------------------------
    | Order Routes  (Customer)
    |--------------------------------------------------------------------------
    | Requires authentication — customers must have a valid Bearer token.
    */
    Route::middleware('auth:api')->prefix('orders')->group(function () {

        /**
         * Place a new Order
         *
         * POST /api/v1/orders
         * Body: { "items": [ { "product_id": 1, "quantity": 2 }, ... ] }
         */
        Route::post('/', [OrderController::class, 'placeOrder'])
            ->name('api.v1.orders.place');
    });

    /*
    |--------------------------------------------------------------------------
    | My Orders Routes (Customer)
    |--------------------------------------------------------------------------
    | Customer self-service order management.
    |
    | GET   /api/v1/my-orders
    | PATCH /api/v1/my-orders/{id}/cancel
    */
    Route::middleware('auth:api')->prefix('my-orders')->group(function () {
        Route::get('/', [OrderController::class, 'myOrders'])
            ->name('api.v1.my-orders.index');

        Route::patch('/{id}/cancel', [OrderController::class, 'cancelMyOrder'])
            ->whereNumber('id')
            ->name('api.v1.my-orders.cancel');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    | Protected by auth:api  (valid Passport token)        → verifies identity
    | Protected by role:Admin (Spatie permission middleware) → verifies authority
    */
    Route::middleware(['auth:api', 'role:Admin'])->prefix('admin')->group(function () {

        /*
        | Admin — Product Management
        | GET    /api/v1/admin/products          → list all products (paginated)
        | POST   /api/v1/admin/products          → create a product
        | PUT    /api/v1/admin/products/{id}     → update a product
        | DELETE /api/v1/admin/products/{id}     → delete a product
        */
        Route::prefix('products')->group(function () {
            Route::get('/', [AdminProductController::class, 'index'])
                ->name('api.v1.admin.products.index');

            Route::post('/', [AdminProductController::class, 'store'])
                ->name('api.v1.admin.products.store');

            Route::put('/{id}', [AdminProductController::class, 'update'])
                ->name('api.v1.admin.products.update');

            Route::delete('/{id}', [AdminProductController::class, 'destroy'])
                ->name('api.v1.admin.products.destroy');
        });

        /*
        | Admin — Order Management
        | GET /api/v1/admin/orders              → list all orders (paginated)
        | PUT /api/v1/admin/orders/{id}/status  → update order status
        */
        Route::prefix('orders')->group(function () {
            Route::get('/', [AdminOrderController::class, 'index'])
                ->name('api.v1.admin.orders.index');

            Route::put('/{id}/status', [AdminOrderController::class, 'updateStatus'])
                ->name('api.v1.admin.orders.updateStatus');
        });

        /*
        | Admin — Customer Management
        | GET /api/v1/admin/customers  → list all users with 'Customer' role
        */
        Route::prefix('customers')->group(function () {
            Route::get('/', [AdminCustomerController::class, 'index'])
                ->name('api.v1.admin.customers.index');
        });
    });
});

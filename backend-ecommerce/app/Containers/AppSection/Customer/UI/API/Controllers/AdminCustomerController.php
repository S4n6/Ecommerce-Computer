<?php

namespace App\Containers\AppSection\Customer\UI\API\Controllers;

use App\Containers\AppSection\Customer\Actions\ListCustomersAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AdminCustomerController
 *
 * Porto UI/API Controller — Admin-only Customer management endpoints.
 * All routes here are guarded by 'role:Admin' middleware in api.php.
 *
 * Routes handled:
 *   GET /api/v1/admin/customers  → index()
 */
class AdminCustomerController extends Controller
{
    // ----------------------------------------------------------------
    // GET /api/v1/admin/customers
    // ----------------------------------------------------------------

    /**
     * List all users with the 'Customer' role (paginated).
     *
     * Query params: page, per_page
     *
     * Response 200:
     * {
     *   "data": [
     *     { "id": 3, "name": "John Doe", "email": "john@example.com", "created_at": "..." }
     *   ],
     *   "links": {...},
     *   "meta": { "current_page": 1, "total": 42, ... }
     * }
     */
    public function index(Request $request, ListCustomersAction $action): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $customers = $action->run($perPage);

        return response()->json($customers);
    }
}

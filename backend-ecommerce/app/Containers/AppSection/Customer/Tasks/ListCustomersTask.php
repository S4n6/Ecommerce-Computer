<?php

namespace App\Containers\AppSection\Customer\Tasks;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * ListCustomersTask
 *
 * Fetches all users who have the 'Customer' role, using Spatie's
 * role() scope. Returns a paginated result so the Admin API
 * can navigate large user bases efficiently.
 *
 * Note: We query directly on the User model here because there is
 * no dedicated Customer Eloquent model — customers are Users with
 * the 'Customer' role assigned via Spatie permission. This keeps
 * the code aligned with the existing project conventions.
 */
class ListCustomersTask
{
    public function run(int $perPage = 15): LengthAwarePaginator
    {
        return User::role('Customer')
            ->select(['id', 'name', 'email', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

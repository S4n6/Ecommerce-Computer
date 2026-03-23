<?php

namespace App\Containers\AppSection\Authorization\UI\API\Controllers;

use App\Containers\AppSection\Authorization\Actions\LoginAction;
use App\Containers\AppSection\Authorization\UI\API\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Auth Controller
 *
 * Handles incoming authentication HTTP requests and delegates
 * the business logic to the LoginAction (Porto pattern).
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly LoginAction $loginAction
    ) {}

    /**
     * Handle a login request and issue a Passport access token.
     *
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->loginAction->run($request->validated());

        return response()->json($result, 200);
    }
}

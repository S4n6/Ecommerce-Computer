<?php

namespace App\Containers\AppSection\Authorization\Tasks;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Issue Passport Token Task
 *
 * Porto "Task" — a single, reusable unit of business logic.
 * Responsible exclusively for validating credentials and
 * issuing a Laravel Passport personal-access token.
 */
class IssuePassportTokenTask
{
    /**
     * Validate credentials and issue a Passport access token.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return array{access_token: string, token_type: string, user: array{id: int, name: string, email: string, roles: \Illuminate\Support\Collection<int, string>}}
     *
     * @throws ValidationException
     */
    public function run(array $credentials): array
    {
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Revoke all previous tokens for a clean single-session experience.
        // Remove this line if you want to support multiple active sessions.
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->accessToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ];
    }
}

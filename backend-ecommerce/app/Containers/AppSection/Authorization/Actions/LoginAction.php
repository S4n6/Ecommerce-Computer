<?php

namespace App\Containers\AppSection\Authorization\Actions;

use App\Containers\AppSection\Authorization\Tasks\IssuePassportTokenTask;

/**
 * Login Action
 *
 * Porto "Action" — orchestrates Tasks to fulfil the Login use-case.
 * An Action may call multiple Tasks; here it delegates solely to
 * IssuePassportTokenTask to keep each layer responsible for a
 * single concern.
 */
class LoginAction
{
    public function __construct(
        private readonly IssuePassportTokenTask $issueTokenTask
    ) {}

    /**
     * Authenticate a user and issue an API access token.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return array{access_token: string, token_type: string, user: array{id: int, name: string, email: string, roles: \Illuminate\Support\Collection<int, string>}}
     */
    public function run(array $credentials): array
    {
        return $this->issueTokenTask->run($credentials);
    }
}

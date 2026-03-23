<?php

namespace App\Containers\AppSection\Authorization\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Login Form Request
 *
 * Validates incoming credentials before they reach the Action layer.
 */
class LoginRequest extends FormRequest
{
    /**
     * Anyone may attempt to log in.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for the login endpoint.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}

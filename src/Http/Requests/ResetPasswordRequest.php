<?php

namespace Athka\AuthKit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => [
                'required', 
                'string', 
                'confirmed', 
                \Illuminate\Validation\Rules\Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min'       => tr('Password must be at least 8 characters long'),
            'password.mixed'     => tr('Password must contain both uppercase and lowercase letters'),
            'password.numbers'   => tr('Password must contain at least one number'),
            'password.symbols'   => tr('Password must contain at least one special character'),
            'password.confirmed' => tr('Password confirmation does not match'),
        ];
    }
}

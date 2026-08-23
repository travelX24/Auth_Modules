<?php

namespace Athka\AuthKit\Http\Controllers\Api;

use Athka\AuthKit\Http\Requests\ChangePasswordRequest;
use Athka\AuthKit\Http\Requests\ForgotPasswordRequest;
use Athka\AuthKit\Http\Requests\ResetPasswordRequest;
use Athka\AuthKit\Support\UiMsg;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function change(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'ok'      => false,
                'message' => __('authkit::auth.password_mismatch') ?: 'The current password you entered is incorrect.',
                'errors'  => [
                    'current_password' => [__('authkit::auth.password_mismatch') ?: 'The current password you entered is incorrect.']
                ],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        $this->revokeApiTokens($user, keepCurrent: true);

        return response()->json([
            'ok'      => true,
            'message' => __('authkit::auth.password_changed') ?: 'Password has been changed successfully.',
        ]);
    }

    public function forgot(ForgotPasswordRequest $request)
    {
        Password::sendResetLink($request->only('email'));

        return response()->json([
            'ok'          => true,
            'message'     => $this->genericResetLinkMessage(),
            'retry_after' => $this->passwordResetThrottleSeconds(),
        ]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password'             => Hash::make((string) $request->input('password')),
                    'remember_token'       => Str::random(60),
                    'must_change_password' => false,
                ])->save();

                $this->revokeApiTokens($user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'ok'      => true,
                'message' => UiMsg::toText($status) ?? __($status),
            ]);
        }

        return response()->json([
            'ok'      => false,
            'message' => UiMsg::toText($status) ?? __($status),
        ], 422);
    }

    private function genericResetLinkMessage(): string
    {
        return function_exists('tr')
            ? tr('If the email exists, we have sent a password reset link.')
            : 'If the email exists, we have sent a password reset link.';
    }

    private function passwordResetThrottleSeconds(): int
    {
        $broker = (string) config('auth.defaults.passwords', 'users');

        return (int) config("auth.passwords.{$broker}.throttle", 60);
    }

    private function revokeApiTokens($user, bool $keepCurrent = false): void
    {
        if (! method_exists($user, 'tokens')) {
            return;
        }

        $query = $user->tokens();

        if ($keepCurrent && method_exists($user, 'currentAccessToken')) {
            $currentToken = $user->currentAccessToken();
            if ($currentToken && isset($currentToken->id)) {
                $query->where('id', '!=', $currentToken->id);
            }
        }

        $query->delete();
    }
}

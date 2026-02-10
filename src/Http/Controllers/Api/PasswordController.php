<?php

namespace Athka\AuthKit\Http\Controllers\Api;

use Athka\AuthKit\Http\Requests\ForgotPasswordRequest;
use Athka\AuthKit\Http\Requests\ResetPasswordRequest;
use Athka\AuthKit\Support\UiMsg;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function forgot(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'ok'      => true,
                'message' => UiMsg::toText($status) ?? __($status),
            ]);
        }

        $msg = UiMsg::toText($status) ?? __($status);

        return response()->json([
            'ok'      => false,
            'message' => $msg,
            'errors'  => ['email' => [$msg]],
        ], 422);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password'       => Hash::make((string) $request->input('password')),
                    'remember_token' => Str::random(60),
                ])->save();
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
}

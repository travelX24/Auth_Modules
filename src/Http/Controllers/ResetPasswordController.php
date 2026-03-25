<?php

namespace Athka\AuthKit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Athka\AuthKit\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function show(Request $request, string $token)
    {
        $email = $request->query('email');

        // Fallback: If email is missing in the URL, try to find it in the database
        if (!$email) {
            $table = \Illuminate\Support\Facades\DB::table('password_reset_tokens')->exists() 
                ? 'password_reset_tokens' 
                : 'password_resets';
            
            $records = \Illuminate\Support\Facades\DB::table($table)->get();
            foreach ($records as $record) {
                if (\Illuminate\Support\Facades\Hash::check($token, $record->token) || $token === $record->token) {
                    $email = $record->email;
                    break;
                }
            }
        }

        return view(config('authkit.views.reset'), [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function update(ResetPasswordRequest $request)
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

        $as = (string) config('authkit.routes.as', 'authkit.');
        $as = $as === '' ? '' : rtrim($as, '.') . '.';

        return $status === Password::PASSWORD_RESET
            ? redirect()->route($as.'login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->withInput($request->only('email'));

    }
}

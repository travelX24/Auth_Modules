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
        $table = \Illuminate\Support\Facades\Schema::hasTable('password_reset_tokens')
            ? 'password_reset_tokens'
            : 'password_resets';
        $expires = config('auth.passwords.users.expire', 60);
        
        $record = $email
            ? \Illuminate\Support\Facades\DB::table($table)->where('email', $email)->first()
            : null;

        // Legacy fallback keeps old links without an email query working.
        if (! $record && ! $email) {
            $record = \Illuminate\Support\Facades\DB::table($table)
                ->where('created_at', '>=', now()->subMinutes($expires))
                ->get()
                ->first(function ($r) use ($token) {
                    return \Illuminate\Support\Facades\Hash::check($token, $r->token) || $token === $r->token;
                });
        }

        $isValid = $record
            && (\Illuminate\Support\Facades\Hash::check($token, $record->token) || $token === $record->token);

        // 1. Check if token exists
        if (!$isValid || !$record) {
            return redirect()->route('authkit.password.request')
                ->withErrors(['email' => __('passwords.token')]);
        }

        // 2. Check if token is expired
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        
        if ($createdAt->addMinutes($expires)->isPast()) {
            // Delete expired token for cleanup
            \Illuminate\Support\Facades\DB::table($table)->where('email', $record->email)->delete();
            
            return redirect()->route('authkit.password.request')
                ->withErrors(['email' => __('passwords.token')]); // This translates to "This password reset token is invalid."
        }

        return view(config('authkit.views.reset'), [
            'token' => $token,
            'email' => $email ?? $record->email,
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

                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

            }
        );

        $as = (string) config('authkit.routes.as', 'authkit.');
        $as = $as === '' ? '' : rtrim($as, '.') . '.';

        return $status === Password::PASSWORD_RESET
            ? redirect()->route($as.'login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->withInput($request->only('email'));

    }
}

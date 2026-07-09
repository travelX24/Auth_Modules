<?php

namespace Athka\AuthKit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function show()
    {
        return view(config('authkit.views.forgot'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        Password::sendResetLink($request->only('email'));

        $message = function_exists('tr')
            ? tr('If the email exists, we have sent a password reset link.')
            : 'If the email exists, we have sent a password reset link.';

        return back()->with('status', $message);
    }
}

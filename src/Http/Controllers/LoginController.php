<?php

namespace Athka\AuthKit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function authenticated(Request $request, $user)
    {
        // تحقق من أن المستخدم هو صاحب النظام بناءً على البريد الإلكتروني
        if ($user->email === 'admin@athkahr.com') {
            // توجيه صاحب النظام إلى واجهة الـ SaaS Dashboard
            return redirect()->route('saas.dashboard');  // تأكد أن المسار موجود في routes/web.php
        }
    
        // للمستخدمين العاديين (إذا كنت تستخدم تعديلات إضافية لهم)
        return redirect()->route('home'); // أو صفحة أخرى
    }
    

    public function show()
    {
        return view(config('authkit.views.login'));
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
    
        $remember = $request->boolean('remember');
    
        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }
    
        $request->session()->regenerate();
    
        // تأكد من التوجيه إلى /saas مباشرة
        return redirect()->route('saas.dashboard');
    }
    

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(config('authkit.redirect_after_logout', '/login'));
    }
}

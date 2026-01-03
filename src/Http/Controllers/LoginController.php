<?php

namespace Athka\AuthKit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * (اختياري) لو تريد تركها موجودة، نخليها ترجع null
     * لأننا بنوحد المنطق داخل redirectAfterLogin()
     */
    public function authenticated(Request $request, $user)
    {
        return null;
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

        $user = $request->user();

        // لو عندك Hook وتريد تستخدمه مستقبلاً
        $resp = $this->authenticated($request, $user);
        if ($resp) {
            return $resp;
        }

        return $this->redirectAfterLogin($request, $user);
    }

    protected function redirectAfterLogin(Request $request, $user)
    {
        // ✅ 1) Company Admin -> /company-admin/hello
        if ($this->isCompanyAdmin($user)) {
            if (Route::has('company-admin.hello')) {
                return redirect()->intended(route('company-admin.hello'));
            }
            // fallback
            return redirect()->intended('/');
        }

        // ✅ 2) System/SaaS Admin -> /saas
        if ($this->isSaasAdmin($user)) {
            if (Route::has('saas.dashboard')) {
                return redirect()->intended(route('saas.dashboard'));
            }
            return redirect()->intended('/saas');
        }

        // ✅ 3) باقي المستخدمين -> حسب إعدادات authkit
        $fallback = config('authkit.redirect_after_login', '/');
        return redirect()->intended($fallback);
    }

    protected function isCompanyAdmin($user): bool
    {
        // Spatie Roles
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('company-admin')) {
                return true;
            }
        }

        // fallback: مرتبط بشركة
        return !empty($user->saas_company_id ?? null);
    }

    protected function isSaasAdmin($user): bool
    {
        // بريد ثابت للـ system owner
        if (($user->email ?? null) === 'admin@athkahr.com') {
            return true;
        }

        // Spatie Roles
        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole(['system-admin', 'saas-admin', 'super-admin']);
        }

        return false;
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(config('authkit.redirect_after_logout', '/login'));
    }
}

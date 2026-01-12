<?php

namespace Athka\AuthKit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function authenticated(Request $request, $user)
    {
        return null;
    }

    public function show(Request $request)
    {
        // ✅ إذا كان المستخدم مسجل دخول بالفعل -> إعادة توجيه حسب الدومين
        if (Auth::check()) {
            $user = Auth::user();
            $host = strtolower($request->getHost());
            
            $base = strtolower(env('TENANT_BASE_DOMAIN', 'athkahr.com'));
            $central = strtolower(env('CENTRAL_DOMAIN', $base));
            
            // ✅ لو نحن على nip.io استخرج IP واصنع base/central ديناميكي
            if (preg_match('/\.(\d{1,3}(?:\.\d{1,3}){3})\.nip\.io$/', $host, $m)) {
                $ip = $m[1];
                $base = "athkahr.$ip.nip.io";
                $central = $base;
            }
            
            $isOnCentralDomain = ($host === $central || $host === 'www.'.$central);
            
            // ✅ إذا كان على الدومين المركزي وكان SaaS Admin -> إعادة توجيه لـ SaaS
            if ($isOnCentralDomain && $this->isSaasAdmin($user)) {
                if (Route::has('saas.dashboard')) {
                    return redirect()->route('saas.dashboard');
                }
                return redirect('/saas');
            }
            
            // ✅ إذا كان على دومين الشركة وكان Company Admin -> إعادة توجيه لصفحة الشركة
            if (! $isOnCentralDomain && $this->isCompanyAdmin($user)) {
                if (Route::has('company-admin.hello')) {
                    return redirect()->route('company-admin.hello');
                }
                return redirect('/company-admin/hello');
            }
            
            // ✅ إذا كان SaaS Admin على دومين الشركة -> إعادة توجيه للدومين المركزي
            if (! $isOnCentralDomain && $this->isSaasAdmin($user)) {
                $scheme = $request->isSecure() ? 'https' : 'http';
                $port = $request->getPort();
                $portPart = in_array($port, [80, 443], true) ? '' : ':'.$port;
                $target = $scheme.'://'.$central.$portPart.'/saas';
                return redirect()->away($target);
            }
            
            // ✅ fallback: إعادة توجيه للصفحة الرئيسية
            return redirect('/');
        }
        
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

        // ✅ التحقق من انتهاء الاشتراك قبل المتابعة
        $subscriptionCheck = $this->checkSubscriptionExpiry($request, $user);
        if ($subscriptionCheck) {
            return $subscriptionCheck;
        }

        $resp = $this->authenticated($request, $user);
        if ($resp) {
            return $resp;
        }

        return $this->redirectAfterLogin($request, $user);
    }

    protected function redirectAfterLogin(Request $request, $user)
    {
        // ✅ 1) Company Admin -> /company-admin/hello (بدون intended نهائياً)
        if ($this->isCompanyAdmin($user)) {
            if (Route::has('company-admin.hello')) {
                return redirect()->route('company-admin.hello');
            }
            return redirect('/');
        }

        // ✅ 2) System/SaaS Admin -> /saas
        if ($this->isSaasAdmin($user)) {
            if (Route::has('saas.dashboard')) {
                return redirect()->route('saas.dashboard');
            }
            return redirect('/saas');
        }

        // ✅ 3) باقي المستخدمين -> intended أو إعدادات authkit
        return redirect()->intended(config('authkit.redirect_after_login', '/'));
    }

    protected function isCompanyAdmin($user): bool
    {
        // Spatie Roles
        if (method_exists($user, 'hasRole') && $user->hasRole('company-admin')) {
            return true;
        }

        // fallback: مرتبط بشركة
        return !empty($user->saas_company_id ?? null);
    }

    protected function isSaasAdmin($user): bool
{
    // ✅ Roles أولاً (الأصح)
    if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['saas-admin','system-admin','super-admin'])) {
        return true;
    }

    // ✅ fallback بريد (اختياري)
    return (($user->email ?? null) === 'admin@athkahr.com');
}

    /**
     * ✅ التحقق من انتهاء الاشتراك
     */
    protected function checkSubscriptionExpiry(Request $request, $user)
    {
        // ✅ فقط للمستخدمين المرتبطين بشركة
        if (! $user->saas_company_id) {
            return null;
        }

        // ✅ التحقق من وجود package Saas
        if (! class_exists(\App\Modules\Saas\Models\SaasCompanyOtherinfo::class)) {
            return null;
        }

        $settings = \App\Modules\Saas\Models\SaasCompanyOtherinfo::where('company_id', $user->saas_company_id)->first();

        if (! $settings || ! $settings->subscription_ends_at) {
            return null;
        }

        // ✅ التحقق من انتهاء الاشتراك
        if ($settings->subscription_ends_at->isPast()) {
            // ✅ حفظ الرسالة أولاً قبل invalidate
            $errorMessage = function_exists('tr')
                ? tr('Your subscription has expired. Please contact system administration to renew your subscription.')
                : 'Your subscription has expired. Please contact system administration to renew your subscription.';

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // ✅ حفظ الرسالة بعد regenerateToken مباشرة
            $request->session()->flash('error', $errorMessage);

            $loginRoute = Route::has('authkit.login')
                ? route('authkit.login')
                : (Route::has('login') ? route('login') : '/login');

            return redirect($loginRoute);
        }

        return null;
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(config('authkit.redirect_after_logout', '/login'));
    }
}

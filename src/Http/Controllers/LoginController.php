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

        // ✅ التحقق من حالة الشركة (تفعيل/إيقاف) قبل المتابعة
        $companyStatusCheck = $this->checkCompanyStatus($request, $user);
        if ($companyStatusCheck) {
            return $companyStatusCheck;
        }

        $subscriptionCheck = $this->checkSubscriptionExpiry($request, $user);
        if ($subscriptionCheck) {
            return $subscriptionCheck;
        }

        $accessTypeCheck = $this->checkWebLoginAccessType($request, $user);
        if ($accessTypeCheck) {
            return $accessTypeCheck;
        }

        $resp = $this->authenticated($request, $user);
        if ($resp) {
            return $resp;
        }

        return $this->redirectAfterLogin($request, $user);

    }

    protected function redirectAfterLogin(Request $request, $user)
    {
        // ✅ 1) Company Admin -> بناء URL على دومين الشركة مباشرة
        if ($this->isCompanyAdmin($user)) {
            $companyUrl = $this->buildCompanyAdminUrl($request, $user);
            if ($companyUrl) {
                return redirect()->away($companyUrl);
            }

            // Fallback: استخدام route إذا لم نتمكن من بناء URL
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

    /**
     * ✅ بناء URL على دومين الشركة مباشرة
     */
    protected function buildCompanyAdminUrl(Request $request, $user): ?string
    {
        if (empty($user->saas_company_id)) {
            return null;
        }

        // ✅ التحقق من وجود package Saas
        $saasCompanyClass = class_exists(\Athka\Saas\Models\SaasCompany::class)
            ? \Athka\Saas\Models\SaasCompany::class
            : (class_exists(\App\Modules\Saas\Models\SaasCompany::class)
                ? \App\Modules\Saas\Models\SaasCompany::class
                : null);

        if (! $saasCompanyClass) {
            return null;
        }

        $company = $saasCompanyClass::find($user->saas_company_id);
        if (! $company || empty($company->primary_domain)) {
            return null;
        }

        $base = strtolower(config('saas.tenant_base_domain', env('TENANT_BASE_DOMAIN', 'athkahr.com')));
        $central = strtolower(config('saas.central_domain', env('CENTRAL_DOMAIN', $base)));

        $host = $request->getHost();
        // ✅ لو نحن على nip.io استخرج IP واصنع base/central ديناميكي
        if (preg_match('/\.(\d{1,3}(?:\.\d{1,3}){3})\.nip\.io$/', $host, $m)) {
            $ip = $m[1];
            $base = "athkahr.$ip.nip.io";
            $central = $base;
        }

        $desiredHost = strtolower($company->primary_domain.'.'.$base);
        $scheme = $request->isSecure() ? 'https' : 'http';
        $port = $request->getPort();
        $portPart = in_array($port, [80, 443], true) ? '' : ':'.$port;

        // ✅ بناء URL كامل على دومين الشركة
        $url = $scheme.'://'.$desiredHost.$portPart.'/hello';

        return $url;
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
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['saas-admin', 'system-admin', 'super-admin'])) {
            return true;
        }

        // ✅ fallback بريد (اختياري)
        return (($user->email ?? null) === 'admin@athkahr.com');
    }

    /**
     * ✅ التحقق من حالة الشركة (تفعيل/إيقاف)
     */
    protected function checkCompanyStatus(Request $request, $user)
    {
        // ✅ فقط للمستخدمين المرتبطين بشركة
        if (! $user->saas_company_id) {
            return null;
        }

        // ✅ التحقق من وجود package Saas
        $saasCompanyClass = class_exists(\Athka\Saas\Models\SaasCompany::class)
            ? \Athka\Saas\Models\SaasCompany::class
            : (class_exists(\App\Modules\Saas\Models\SaasCompany::class)
                ? \App\Modules\Saas\Models\SaasCompany::class
                : null);

        if (! $saasCompanyClass) {
            return null;
        }

        $company = $saasCompanyClass::find($user->saas_company_id);

        if (! $company) {
            return null;
        }

        // ✅ التحقق من حالة الشركة
        if (! $company->is_active) {
            // ✅ حفظ الرسالة أولاً قبل invalidate
            $errorMessage = function_exists('tr')
                ? tr('Your company account is currently deactivated. Please contact system administration to activate your company account.')
                : 'Your company account is currently deactivated. Please contact system administration to activate your company account.';

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
        $model = class_exists(\Athka\Saas\Models\SaasCompanyOtherinfo::class)
    ? \Athka\Saas\Models\SaasCompanyOtherinfo::class
    : (class_exists(\App\Modules\Saas\Models\SaasCompanyOtherinfo::class)
        ? \App\Modules\Saas\Models\SaasCompanyOtherinfo::class
        : null);

if (! $model) {
    return null;
}

$settings = $model::where('company_id', $user->saas_company_id)->first();



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

    /**
     *  منع تسجيل الدخول للويب إذا كان الحساب "تطبيق الموارد البشرية فقط"
     * (يبقى استعماله للموبايل/API حسب نظامك)
     */
    protected function checkWebLoginAccessType(Request $request, $user)
    {
        $accessType = $user->access_type ?? 'system_and_app';

        if ($accessType === 'hr_app_only') {
            $errorMessage = function_exists('tr')
                ? tr('This account is licensed for the HR mobile app only.')
                : 'This account is licensed for the HR mobile app only.';

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $request->session()->flash('error', $errorMessage);

            $loginRoute = Route::has('authkit.login')
                ? route('authkit.login')
                : (Route::has('login') ? route('login') : '/login');

            return redirect($loginRoute);
        }

        if ($accessType === 'system_and_app') {
            $hasRolesRelation = method_exists($user, 'roles');
            $rolesCount = $hasRolesRelation ? $user->roles()->count() : 0;

            // ✅ Allow users who have custom direct permissions (no role needed)
            $hasCustomPermissions = (bool) ($user->has_custom_permissions ?? false);

            if ($rolesCount === 0 && ! $hasCustomPermissions && ! $this->isSaasAdmin($user)) {
                $errorMessage = function_exists('tr')
                    ? tr('No role is assigned to your account. Please contact system administration.')
                    : 'No role is assigned to your account. Please contact system administration.';

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $request->session()->flash('error', $errorMessage);

                $loginRoute = Route::has('authkit.login')
                    ? route('authkit.login')
                    : (Route::has('login') ? route('login') : '/login');

                return redirect($loginRoute);
            }
        }

        return null;
    }

}

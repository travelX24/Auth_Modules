<?php

namespace Athka\AuthKit\Http\Controllers\Api;

use Athka\AuthKit\Http\Controllers\LoginController as WebLoginController;
use Athka\AuthKit\Http\Requests\LoginRequest;
use Athka\AuthKit\Support\UiMsg;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends WebLoginController
{
    public function login(LoginRequest $request)
    {
        $email    = (string) $request->input('email');
        $password = (string) $request->input('password');

        $userModel = config('auth.providers.users.model');

        if (!$userModel || !class_exists($userModel)) {
            return response()->json([
                'ok'      => false,
                'error'   => 'server_misconfigured',
                'message' => UiMsg::toText('Something went wrong') ?? 'Something went wrong',
            ], 500);
        }

        $user = $userModel::where('email', $email)->first();

        if (!$user || !Hash::check($password, (string) ($user->password ?? ''))) {
            return response()->json([
                'ok'      => false,
                'error'   => 'invalid_credentials',
                'message' => UiMsg::toText('auth.failed') ?? 'Invalid email or password',
            ], 401);
        }

        /**
         * ✅ Mobile API only for employee users
         */
        if ((bool) config('authkit.api.employees_only', true)) {
            $hasEmployeeId = !empty($user->employee_id);

            $employeeExists = $hasEmployeeId;
            if ($hasEmployeeId && method_exists($user, 'employee')) {
                $employeeExists = $user->employee()->exists();
            }

            if (! $employeeExists) {
                $msg = function_exists('tr')
                    ? tr('This account is not allowed to use the mobile app.')
                    : 'This account is not allowed to use the mobile app.';

                return response()->json([
                    'ok'      => false,
                    'error'   => 'not_mobile_user',
                    'message' => $msg,
                ], 403);
            }

            if ($user->getAttribute('is_active') === false) {
                $msg = function_exists('tr')
                    ? tr('Your account is currently inactive.')
                    : 'Your account is currently inactive.';

                return response()->json([
                    'ok'      => false,
                    'error'   => 'user_inactive',
                    'message' => $msg,
                ], 403);
            }
        }

        if ($resp = $this->checkCompanyStatusApi($user)) {
            return $resp;
        }

        if ($resp = $this->checkSubscriptionExpiryApi($user)) {
            return $resp;
        }

        if (!method_exists($user, 'createToken')) {
            return response()->json([
                'ok'      => false,
                'error'   => 'sanctum_missing',
                'message' => 'Sanctum is not configured. Add HasApiTokens to your User model and install laravel/sanctum.',
            ], 500);
        }

        $tokenName  = (string) config('authkit.api.token_name', 'mobile');
        $abilities  = (array)  config('authkit.api.token_abilities', ['*']);
        $ua         = Str::limit((string) $request->userAgent(), 80, '');
        $tokenLabel = $tokenName . '|' . $ua;

        $plainToken = $user->createToken($tokenLabel, $abilities)->plainTextToken;

        return response()->json([
            'ok'           => true,
            'token_type'   => 'Bearer',
            'access_token' => $plainToken,
            'user'         => $this->buildUserPayload($user), // ✅ NEW
            'next'         => $this->nextForUser($request, $user),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'ok'   => true,
            'user' => $this->buildUserPayload($user),
            'next' => $this->nextForUser($request, $user),
        ]);
    }

    public function bootstrap(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'ok'   => true,
            'user' => $this->buildUserPayload($user),
            'next' => $this->nextForUser($request, $user),
            'meta' => [
                'server_time' => now()->toDateTimeString(),
            ],
        ]);
    }


    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && method_exists($user, 'currentAccessToken')) {
            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
            }
        }

        return response()->json([
            'ok'      => true,
            'message' => UiMsg::toText('Logged out') ?? 'Logged out',
        ]);
    }

    protected function nextForUser(Request $request, $user): array
    {
        if ((bool) config('authkit.api.employees_only', true)) {
            return [
                'type'         => 'employee',
                'redirect_url' => null,
            ];
        }

        if ($this->isCompanyAdmin($user)) {
            return [
                'type'         => 'company-admin',
                'redirect_url' => $this->buildCompanyAdminUrl($request, $user),
            ];
        }

        if ($this->isSaasAdmin($user)) {
            return [
                'type'         => 'saas-admin',
                'redirect_url' => url('/saas'),
            ];
        }

        return [
            'type'         => 'user',
            'redirect_url' => url((string) config('authkit.redirect_after_login', '/')),
        ];
    }

    protected function checkCompanyStatusApi($user)
    {
        if (empty($user->saas_company_id)) {
            return null;
        }

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

        if (! $company->is_active) {
            $msg = function_exists('tr')
                ? tr('Your company account is currently deactivated. Please contact system administration to activate your company account.')
                : 'Your company account is currently deactivated. Please contact system administration to activate your company account.';

            return response()->json([
                'ok'      => false,
                'error'   => 'company_deactivated',
                'message' => $msg,
            ], 403);
        }

        return null;
    }

    protected function checkSubscriptionExpiryApi($user)
    {
        if (empty($user->saas_company_id)) {
            return null;
        }

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

        $endsAt = $settings->subscription_ends_at;
        if (is_string($endsAt)) {
            $endsAt = Carbon::parse($endsAt);
        }

        if ($endsAt instanceof \Carbon\CarbonInterface && $endsAt->isPast()) {
            $msg = function_exists('tr')
                ? tr('Your subscription has expired. Please contact system administration to renew your subscription.')
                : 'Your subscription has expired. Please contact system administration to renew your subscription.';

            return response()->json([
                'ok'      => false,
                'error'   => 'subscription_expired',
                'message' => $msg,
            ], 403);
        }

        return null;
    }

    protected function buildUserPayload($user): array
    {
        // ✅ فقط employee على User (تجنب department/jobTitle على User)
        if (method_exists($user, 'loadMissing') && method_exists($user, 'employee')) {
            $user->loadMissing(['employee']);
        }

        // ✅ Company
        $company = null;
        $companyInfo = null;

        if (!empty($user->saas_company_id)) {
            $saasCompanyClass = class_exists(\Athka\Saas\Models\SaasCompany::class)
                ? \Athka\Saas\Models\SaasCompany::class
                : (class_exists(\App\Modules\Saas\Models\SaasCompany::class)
                    ? \App\Modules\Saas\Models\SaasCompany::class
                    : null);

            $saasOtherInfoClass = class_exists(\Athka\Saas\Models\SaasCompanyOtherinfo::class)
                ? \Athka\Saas\Models\SaasCompanyOtherinfo::class
                : (class_exists(\App\Modules\Saas\Models\SaasCompanyOtherinfo::class)
                    ? \App\Modules\Saas\Models\SaasCompanyOtherinfo::class
                    : null);

            if ($saasCompanyClass) {
                $company = $saasCompanyClass::find($user->saas_company_id);
            }

            if ($saasOtherInfoClass) {
                $companyInfo = $saasOtherInfoClass::where('company_id', $user->saas_company_id)->first();
            }
        }

        // ✅ Employee + nested relations safely
        $employee = null;

        if (!empty($user->employee_id) && isset($user->employee)) {
            $employee = $user->employee;

            if ($employee && method_exists($employee, 'loadMissing')) {
                $rels = [];
                if (method_exists($employee, 'department')) $rels[] = 'department';
                if (method_exists($employee, 'jobTitle'))   $rels[] = 'jobTitle';
                if (method_exists($employee, 'job_title'))  $rels[] = 'job_title';
                if (method_exists($employee, 'documents'))  $rels[] = 'documents';

                if (!empty($rels)) {
                    $employee->loadMissing($rels);
                }
            }
        }

        // ✅ Roles / Permissions
        $roles = [];
        $permissions = [];

        if (method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames()->values()->all();
        }

        if (method_exists($user, 'getAllPermissions')) {
            $permissions = $user->getAllPermissions()->pluck('name')->values()->all();
        }

        $subscriptionEndsAt = null;
        if ($companyInfo && isset($companyInfo->subscription_ends_at)) {
            $endsAt = $companyInfo->subscription_ends_at;
            $subscriptionEndsAt = is_object($endsAt) && method_exists($endsAt, 'toDateTimeString')
                ? $endsAt->toDateTimeString()
                : (is_string($endsAt) ? $endsAt : null);
        }

        return [
            'id'              => $user->id,
            'name'            => $user->name ?? null,
            'email'           => $user->email ?? null,
            'saas_company_id' => $user->saas_company_id ?? null,
            'employee_id'     => $user->employee_id ?? null,

            'employee' => $employee ? [
                'id'       => $employee->id ?? null,
                'name_ar'  => $employee->name_ar ?? null,
                'name_en'  => $employee->name_en ?? null,
                'mobile'   => $employee->mobile ?? null,
                'gender'   => $employee->gender ?? null,
                'personal_photo_path' => $employee->documents->where('type', 'personal_photo')->first()?->file_path 
                    ?? $employee->personal_photo_path 
                    ?? null,

                'department' => (method_exists($employee, 'department') && $employee->relationLoaded('department') && $employee->department)
                    ? [
                        'id'   => $employee->department->id ?? null,
                        'name' => $employee->department->name ?? null,
                        'code' => $employee->department->code ?? null,
                    ]
                    : null,

                'job_title' => (
                    (method_exists($employee, 'jobTitle') && $employee->relationLoaded('jobTitle') && $employee->jobTitle)
                    ? $employee->jobTitle
                    : ((method_exists($employee, 'job_title') && $employee->relationLoaded('job_title') && $employee->job_title) ? $employee->job_title : null)
                )
                    ? [
                        'id'   => (method_exists($employee, 'jobTitle') && $employee->jobTitle) ? ($employee->jobTitle->id ?? null) : ($employee->job_title->id ?? null),
                        'name' => (method_exists($employee, 'jobTitle') && $employee->jobTitle) ? ($employee->jobTitle->name ?? null) : ($employee->job_title->name ?? null),
                        'code' => (method_exists($employee, 'jobTitle') && $employee->jobTitle) ? ($employee->jobTitle->code ?? null) : ($employee->job_title->code ?? null),
                    ]
                    : null,
            ] : null,

            'company' => $company ? [
                'id'                 => $company->id ?? null,
                'legal_name_ar'      => $company->legal_name_ar ?? null,
                'legal_name_en'      => $company->legal_name_en ?? null,
                'primary_domain'     => $company->primary_domain ?? null,
                'is_active'          => $company->is_active ?? null,
                'subscription_ends_at' => $subscriptionEndsAt,
                'allowed_users'        => $companyInfo?->allowed_users,
                'official_email'       => $company->official_email ?? null,
                'phone_1'              => $company->phone_1 ?? null,
            ] : null,

            'roles'       => $roles,
            'permissions' => $permissions,
        ];
    }
}

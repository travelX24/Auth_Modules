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
    private array $saasCompanyCache = [];

    private array $saasCompanyInfoCache = [];

    private static ?bool $employeeDocumentsTableExists = null;

    private static ?bool $employeeDocumentsHasDeletedAt = null;

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

        // ✅ منع الدخول لو الحساب غير نشط (دائم للموبايل)
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

        /**
         * ✅ Mobile API only for employee users
         * (هذا يحدد من يُسمح له بالموبايل من حيث كونه موظف)
         */
        if ((bool) config('authkit.api.employees_only', true)) {
            $hasEmployeeId = ! empty($user->employee_id);

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
        }

        if ($resp = $this->checkCompanyStatusApi($user)) {
            return $resp;
        }

        if ($resp = $this->checkSubscriptionExpiryApi($user)) {
            return $resp;
        }

        // ✅ تحقق الرخصة للموبايل (access_type)
        if ($resp = $this->checkMobileLicenseApi($user)) {
            return $resp;
        }

        // ✅ تقييد الأجهزة (جهاز واحد لكل موظف)
        $incomingDeviceId = $request->input('device_id');

        // Skip check based on app environment header and server debug settings
        $appEnv = $request->header('X-App-Env');
        if ($appEnv === 'prod') {
            // Force strict mode for production app builds
            $skipDeviceCheck = false;
        } else {
            // Allow bypassing for dev builds if server allows it
            $skipDeviceCheck = config('authkit.api.skip_device_check') || config('app.debug', false);
        }

        if (!empty($incomingDeviceId) && !$skipDeviceCheck) {
            // Check if device is used by ANOTHER ACTIVE employee in the SAME company
            $deviceUsedByAnother = $userModel::where('device_id', $incomingDeviceId)
                ->where('id', '!=', $user->id)
                ->where('saas_company_id', $user->saas_company_id)
                ->where('is_active', true) // Only block if the other user is actually active
                ->first();
                
            if ($deviceUsedByAnother) {
                return response()->json([
                    'ok'      => false,
                    'error'   => 'device_linked_to_other',
                    'message' => function_exists('tr') ? tr('This device is already linked to another employee.') : 'This device is already linked to another employee.',
                ], 403);
            }

            // If the device ID was linked to an INACTIVE user in the SAME company, clear it from them so this user can take it
            $userModel::where('device_id', $incomingDeviceId)
                ->where('id', '!=', $user->id)
                ->where('saas_company_id', $user->saas_company_id)
                ->where('is_active', false)
                ->update(['device_id' => null]);

            if (empty($user->device_id)) {
                $user->device_id = $incomingDeviceId;
                $user->save();
            } else {
                // --- تنظيف تلقائي للبيانات القديمة في قاعدة البيانات ---
                $storedDeviceIsUnique = false;
                if (strlen($user->device_id) === 36 && substr_count($user->device_id, '-') === 4) {
                    $storedDeviceIsUnique = true;
                } elseif (strlen($user->device_id) === 16 && ctype_xdigit($user->device_id)) {
                    $storedDeviceIsUnique = true;
                } elseif (strpos($user->device_id, 'athka_device_') === 0) {
                    $storedDeviceIsUnique = true;
                }

                // إذا كان المعرف المخزن قديماً (اسم موديل)، نقوم بتحديثه بالمعرف الفريد الجديد
                if (!$storedDeviceIsUnique) {
                    $user->device_id = $incomingDeviceId;
                    $user->save();
                } elseif ($user->device_id !== $incomingDeviceId) {
                    return response()->json([
                        'ok'      => false,
                        'error'   => 'device_mismatch',
                        'message' => function_exists('tr') ? tr('Your account is linked to another device.') : 'Your account is linked to another device.',
                    ], 403);
                }
            }
        } else if (!empty($incomingDeviceId) && $skipDeviceCheck) {
            // Even if skipping check, store the device ID if not set
            if (empty($user->device_id)) {
                $user->device_id = $incomingDeviceId;
                $user->save();
            }
        } else {
            // Optional: Block login completely if device_id is missing
            // Uncomment the next lines if older versions of the app should be strictly blocked from logging in.
            // return response()->json(['ok' => false, 'error' => 'device_id_required', 'message' => tr('Please update the app to the latest version.')], 403);
        }

        if (! method_exists($user, 'createToken')) {
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

        $plainToken = $this->createPlainTextToken(
            $user,
            $tokenLabel,
            $abilities,
            $this->apiTokenExpiresAt()
        );

        return response()->json([
            'ok'           => true,
            'token_type'   => 'Bearer',
            'access_token' => $plainToken,
            'user'         => $this->buildUserPayload($user),
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

        $company = $this->resolveSaasCompany((int) $user->saas_company_id);

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

        $settings = $this->resolveSaasCompanyInfo((int) $user->saas_company_id);

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

    /**
     * ✅ تحقق الرخصة للموبايل حسب access_type
     * - hr_app_only: لازم يكون مربوط بموظف فعلياً
     * - system_and_app: مسموح للموبايل أيضاً (حسب تصميمك الحالي)
     */
    protected function checkMobileLicenseApi($user)
    {
        $accessType = $user->access_type ?? 'system_and_app';

        // ✅ لو قيمة غير معروفة اعتبرها نظام+تطبيق (Backward compatible)
        if (! in_array($accessType, ['system_and_app', 'hr_app_only'], true)) {
            $accessType = 'system_and_app';
        }

        // ✅ إذا الرخصة "تطبيق فقط" لازم يكون الحساب مربوط بموظف
        if ($accessType === 'hr_app_only') {
            $hasEmployeeId = ! empty($user->employee_id);

            // لو العلاقة موجودة نتأكد انه الموظف موجود فعلاً
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
        }

        return null;
    }

    protected function buildUserPayload($user): array
    {
        // ⚡ Eager-load relations to reduce database query roundtrips from 20+ to only 6-8
        if (method_exists($user, 'loadMissing')) {
            $relations = [];
            if (method_exists($user, 'employee')) {
                $relations[] = 'employee';
                
                $employeeClass = \Athka\Employees\Models\Employee::class;
                if (class_exists($employeeClass)) {
                    $empInstance = new $employeeClass;
                    if (method_exists($empInstance, 'department')) $relations[] = 'employee.department';
                    if (method_exists($empInstance, 'jobTitle'))   $relations[] = 'employee.jobTitle';
                    if (method_exists($empInstance, 'job_title'))  $relations[] = 'employee.job_title';
                    if (method_exists($empInstance, 'branch'))     $relations[] = 'employee.branch';
                }
            }
            if (method_exists($user, 'roles')) {
                $relations[] = 'roles.permissions';
            }
            if (method_exists($user, 'permissions')) {
                $relations[] = 'permissions';
            }

            if (! empty($relations)) {
                $user->loadMissing($relations);
            }
        }

        // ✅ Company
        $company = null;
        $companyInfo = null;

        if (! empty($user->saas_company_id)) {
            $company = $this->resolveSaasCompany((int) $user->saas_company_id);
            $companyInfo = $this->resolveSaasCompanyInfo((int) $user->saas_company_id);
        }

        // ✅ Employee + nested relations safely
        $employee = null;

        if (! empty($user->employee_id) && isset($user->employee)) {
            $employee = $user->employee;
        }

        $personalPhotoPath = null;
        if ($employee) {
            $personalPhotoPath = $employee->personal_photo_path ?? null;

            if (method_exists($employee, 'relationLoaded') && $employee->relationLoaded('documents')) {
                $personalPhotoPath = $employee->documents->where('type', 'personal_photo')->first()?->file_path
                    ?? $personalPhotoPath;
            } elseif ($this->hasEmployeeDocumentsTable()) {
                $photoQuery = \Illuminate\Support\Facades\DB::table('employee_documents')
                    ->where('employee_id', $employee->id)
                    ->where('type', 'personal_photo');

                if ($this->employeeDocumentsHasDeletedAtColumn()) {
                    $photoQuery->whereNull('deleted_at');
                }

                $personalPhotoPath = $photoQuery->orderBy('id')->value('file_path')
                    ?? $personalPhotoPath;
            }
        }

        // ✅ Roles / Permissions
        $roles = [];
        $permissions = [];

        if ($user->relationLoaded('roles')) {
            $roles = $user->roles->pluck('name')->values()->all();
        } elseif (method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames()->values()->all();
        }

        if ($user->relationLoaded('permissions') && $user->relationLoaded('roles')) {
            $permissions = $user->permissions->merge(
                $user->roles->flatMap(function ($role) {
                    return $role->permissions ?? collect();
                })
            )->pluck('name')->unique()->values()->all();
        } elseif (method_exists($user, 'getAllPermissions')) {
            $permissions = $user->getAllPermissions()->pluck('name')->values()->all();
        }

        $subscriptionEndsAt = null;
        if ($companyInfo && isset($companyInfo->subscription_ends_at)) {
            $endsAt = $companyInfo->subscription_ends_at;
            $subscriptionEndsAt = is_object($endsAt) && method_exists($endsAt, 'toDateTimeString')
                ? $endsAt->toDateTimeString()
                : (is_string($endsAt) ? $endsAt : null);
        }

        $annualLeaveDays = null;
        if ($employee) {
            try {
                $annualLeaveDays = method_exists($employee, 'calculateLeaveEntitlement')
                    ? (float) $employee->calculateLeaveEntitlement()
                    : (float) (
                        $employee->is_transferred_employee
                            ? (($employee->opening_leave_balance ?? 0) + ($employee->leave_balance_adjustments ?? 0))
                            : (($employee->annual_leave_days ?? 30) + ($employee->leave_balance_adjustments ?? 0))
                    );
            } catch (\Throwable $e) {
                $annualLeaveDays = (float) (
                    $employee->is_transferred_employee
                        ? (($employee->opening_leave_balance ?? 0) + ($employee->leave_balance_adjustments ?? 0))
                        : (($employee->annual_leave_days ?? 30) + ($employee->leave_balance_adjustments ?? 0))
                );
            }
        }

        return [
            'id'              => $user->id,
            'name'            => $user->name ?? null,
            'email'           => $user->email ?? null,
            'saas_company_id' => $user->saas_company_id ?? null,
            'employee_id'     => $user->employee_id ?? null,

            // ✅ جديد (مهم للتطبيق)
            'access_type'     => $user->access_type ?? 'system_and_app',
            'access_scope'    => $user->access_scope ?? null,
            'is_active'       => $user->is_active ?? true,

            'employee' => $employee ? [
                'id'      => $employee->id ?? null,
                'name_ar' => $employee->name_ar ?? null,
                'name_en' => $employee->name_en ?? null,
                'mobile'  => $employee->mobile ?? null,
                'gender'  => $employee->gender ?? null,
                'personal_photo_path' => $personalPhotoPath,
                'annual_leave_days' => $annualLeaveDays,

                'department' => (method_exists($employee, 'department') && $employee->relationLoaded('department') && $employee->department)
                    ? [
                        'id'   => $employee->department->id ?? null,
                        'name' => $employee->department->name ?? null,
                        'code' => $employee->department->code ?? null,
                    ]
                    : null,
                
                'branch' => (method_exists($employee, 'branch') && $employee->relationLoaded('branch') && $employee->branch)
                    ? [
                        'id'   => $employee->branch->id ?? null,
                        'name' => $employee->branch->name ?? null,
                        'code' => $employee->branch->code ?? null,
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
                'id'                  => $company->id ?? null,
                'legal_name_ar'       => $company->legal_name_ar ?? null,
                'legal_name_en'       => $company->legal_name_en ?? null,
                'primary_domain'      => $company->primary_domain ?? null,
                'is_active'           => $company->is_active ?? null,
                'subscription_ends_at'=> $subscriptionEndsAt,
                'allowed_users'       => $companyInfo?->allowed_users,
                'official_email'      => $company->official_email ?? null,
                'phone_1'             => $company->phone_1 ?? null,
                'time_format'         => $companyInfo?->datetime_format ?? '24h',
            ] : null,

            'roles'       => $roles,
            'permissions' => $permissions,
            'is_approver' => ($employee && (\Illuminate\Support\Facades\DB::table('employees')->where('manager_id', $employee->id)->exists() 
                || \Illuminate\Support\Facades\DB::table('approval_policy_steps')->where('approver_id', $employee->id)->exists()
                || \Illuminate\Support\Facades\DB::table('approval_tasks')->where('approver_employee_id', $employee->id)->exists())),
        ];
    }

    protected function apiTokenExpiresAt(): ?Carbon
    {
        $minutes = config('authkit.api.token_expiration_minutes');

        if ($minutes === null || $minutes === '' || (int) $minutes <= 0) {
            return null;
        }

        return now()->addMinutes((int) $minutes);
    }

    protected function createPlainTextToken($user, string $tokenLabel, array $abilities, ?Carbon $expiresAt): string
    {
        $method = new \ReflectionMethod($user, 'createToken');

        if ($expiresAt && $method->getNumberOfParameters() >= 3) {
            return $user->createToken($tokenLabel, $abilities, $expiresAt)->plainTextToken;
        }

        return $user->createToken($tokenLabel, $abilities)->plainTextToken;
    }

    protected function hasEmployeeDocumentsTable(): bool
    {
        return self::$employeeDocumentsTableExists ??= \Illuminate\Support\Facades\Schema::hasTable('employee_documents');
    }

    protected function employeeDocumentsHasDeletedAtColumn(): bool
    {
        if (! $this->hasEmployeeDocumentsTable()) {
            return false;
        }

        return self::$employeeDocumentsHasDeletedAt ??= \Illuminate\Support\Facades\Schema::hasColumn('employee_documents', 'deleted_at');
    }

    protected function resolveSaasCompany(int $companyId)
    {
        if ($companyId <= 0) {
            return null;
        }

        if (array_key_exists($companyId, $this->saasCompanyCache)) {
            return $this->saasCompanyCache[$companyId];
        }

        $class = class_exists(\Athka\Saas\Models\SaasCompany::class)
            ? \Athka\Saas\Models\SaasCompany::class
            : (class_exists(\App\Modules\Saas\Models\SaasCompany::class)
                ? \App\Modules\Saas\Models\SaasCompany::class
                : null);

        return $this->saasCompanyCache[$companyId] = $class ? $class::find($companyId) : null;
    }

    protected function resolveSaasCompanyInfo(int $companyId)
    {
        if ($companyId <= 0) {
            return null;
        }

        if (array_key_exists($companyId, $this->saasCompanyInfoCache)) {
            return $this->saasCompanyInfoCache[$companyId];
        }

        $class = class_exists(\Athka\Saas\Models\SaasCompanyOtherinfo::class)
            ? \Athka\Saas\Models\SaasCompanyOtherinfo::class
            : (class_exists(\App\Modules\Saas\Models\SaasCompanyOtherinfo::class)
                ? \App\Modules\Saas\Models\SaasCompanyOtherinfo::class
                : null);

        return $this->saasCompanyInfoCache[$companyId] = $class
            ? $class::where('company_id', $companyId)->first()
            : null;
    }
}

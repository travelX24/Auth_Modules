<?php
return [
    'enabled' => true,

    'redirect_after_login'  => '/saas',
    'redirect_after_logout' => '/login',

    'routes' => [
        'prefix' => env('AUTHKIT_ROUTE_PREFIX', ''),
        'as'     => env('AUTHKIT_ROUTE_AS', 'authkit.'),
    ],

    // ✅ NEW: API Settings (للتطبيق)
    'api' => [
        'enabled'         => env('AUTHKIT_API_ENABLED', true),
        'prefix'          => env('AUTHKIT_API_PREFIX', 'api/auth'),
        'as'              => env('AUTHKIT_API_AS', 'authkit.api.'),
        'middleware'      => ['api'],
        'auth_middleware' => ['auth:sanctum'],
        'token_name'      => env('AUTHKIT_TOKEN_NAME', 'mobile'),
        'token_abilities' => ['*'],

        'employees_only'  => env('AUTHKIT_API_EMPLOYEES_ONLY', true),
        'skip_device_check' => env('AUTHKIT_API_SKIP_DEVICE_CHECK', false),
    ],


    // ✅ NEW (اختياري): Deep Link / Frontend URL
    // مثال للتطبيق: myapp://reset-password?token={token}&email={email}
    // لو تركته null بيستخدم رابط الويب الحالي /reset-password/{token}?email=...
    'password_reset_url' => env('AUTHKIT_PASSWORD_RESET_URL', null),

    'views' => [
        'login'  => 'authkit::auth.login',
        'forgot' => 'authkit::auth.forgot-password',
        'reset'  => 'authkit::auth.reset-password',
    ],

    'mail' => [
        'reset' => 'authkit::mail.reset-password',
    ],
];

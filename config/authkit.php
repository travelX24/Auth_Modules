<?php

return [
    'enabled' => true, // ✅ فعلها هنا (وليس داخل packages)

    // ✅ Redirects (يمكنك تغييرها من مشروعك المستهلك)
    'redirect_after_login'  => '/',
    'redirect_after_logout' => '/login',


    'routes' => [
    'prefix' => env('AUTHKIT_ROUTE_PREFIX', ''),      // الافتراضي: /login
    'as'     => env('AUTHKIT_ROUTE_AS', 'authkit.'),  // أسماء الروتات
],


    'views' => [
        'login'  => 'authkit::auth.login',
        'forgot' => 'authkit::auth.forgot-password',
        'reset'  => 'authkit::auth.reset-password',
    ],

    'mail' => [
        'reset' => 'authkit::mail.reset-password',
    ],
];

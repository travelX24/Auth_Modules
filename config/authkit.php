<?php

return [
    'enabled' => true, // ✅ فعلها هنا (وليس داخل packages)

    'routes' => [
        'prefix' => 'authkit',   // ✅ يمنع تعارض /login مع الموديول
        'as'     => 'authkit.',  // ✅ يمنع تعارض أسماء الراوت login/password.request...
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

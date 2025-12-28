<?php

namespace Athka\AuthKit\Support;

class UiMsg
{
    public static function map(): array
    {
        return [
            // Login
            'authkit::auth.invalid' => 'Invalid email or password',
            'auth.failed'           => 'Invalid email or password',

            // Password reset broker
            'passwords.user'        => "We can't find a user with that email address.",
            'passwords.throttled'   => 'Please wait before retrying.',
            'passwords.sent'        => 'We have emailed your password reset link.',
            'passwords.reset'       => 'Your password has been reset.',
            'passwords.token'       => 'This password reset token is invalid.',
            'passwords.password'    => 'Password confirmation does not match.',

            // Validation keys (لو رجعت تظهر keys)
            'validation.required'   => 'This field is required',
            'validation.email'      => 'Please enter a valid email address',
            'validation.confirmed'  => 'Password confirmation does not match.',
            'validation.min'        => 'Password is too short.',
        ];
    }

    public static function hideInline(string $key = null): bool
    {
        if (!$key) return false;

        return in_array($key, [
            'authkit::auth.invalid',
            'auth.failed',
        ], true);
    }

    public static function toText($value): ?string
    {
        if (!is_string($value)) return null;

        $value = trim($value);
        if ($value === '') return null;

        $original = $value;
        $map = self::map();

        // 1) key معروف => عبارة مفهومة
        if (isset($map[$original])) {
            $value = $map[$original];
        }

        // 2) لو هو key وما له mapping => لا تعرض key للمستخدم
        $looksLikeKey =
            str_contains($original, '::') ||
            preg_match('/^[a-z0-9_]+(\.[a-z0-9_]+)+$/i', $original);

        if ($looksLikeKey && !isset($map[$original])) {
            return function_exists('tr') ? tr('Something went wrong') : 'Something went wrong';
        }

        // 3) لو عربي جاهز لا تمرره على tr
        if (preg_match('/\p{Arabic}/u', $value)) return $value;

        // 4) مرره على tr (يتخزن في DB عندك)
        return function_exists('tr') ? tr($value) : $value;
    }
}

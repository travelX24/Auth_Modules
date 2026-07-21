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

            // Validation keys (if Laravel returns keys instead of readable messages)
            'validation.required'            => ['ar' => "\u{0647}\u{0630}\u{0627} \u{0627}\u{0644}\u{062D}\u{0642}\u{0644} \u{0645}\u{0637}\u{0644}\u{0648}\u{0628}.", 'en' => 'This field is required.'],
            'validation.required_if'         => ['ar' => "\u{0647}\u{0630}\u{0627} \u{0627}\u{0644}\u{062D}\u{0642}\u{0644} \u{0645}\u{0637}\u{0644}\u{0648}\u{0628}.", 'en' => 'This field is required.'],
            'validation.email'               => ['ar' => "\u{064A}\u{0631}\u{062C}\u{0649} \u{0625}\u{062F}\u{062E}\u{0627}\u{0644} \u{0628}\u{0631}\u{064A}\u{062F} \u{0625}\u{0644}\u{0643}\u{062A}\u{0631}\u{0648}\u{0646}\u{064A} \u{0635}\u{062D}\u{064A}\u{062D}.", 'en' => 'Please enter a valid email address.'],
            'validation.unique'              => ['ar' => "\u{0647}\u{0630}\u{0647} \u{0627}\u{0644}\u{0642}\u{064A}\u{0645}\u{0629} \u{0645}\u{0633}\u{062A}\u{062E}\u{062F}\u{0645}\u{0629} \u{0645}\u{0633}\u{0628}\u{0642}\u{0627}.", 'en' => 'This value is already used.'],
            'validation.in'                  => ['ar' => "\u{0627}\u{0644}\u{0642}\u{064A}\u{0645}\u{0629} \u{0627}\u{0644}\u{0645}\u{062E}\u{062A}\u{0627}\u{0631}\u{0629} \u{063A}\u{064A}\u{0631} \u{0635}\u{062D}\u{064A}\u{062D}\u{0629}.", 'en' => 'The selected value is invalid.'],
            'validation.exists'              => ['ar' => "\u{0627}\u{0644}\u{0642}\u{064A}\u{0645}\u{0629} \u{0627}\u{0644}\u{0645}\u{062E}\u{062A}\u{0627}\u{0631}\u{0629} \u{063A}\u{064A}\u{0631} \u{0635}\u{062D}\u{064A}\u{062D}\u{0629}.", 'en' => 'The selected value is invalid.'],
            'validation.confirmed'           => ['ar' => "\u{062A}\u{0623}\u{0643}\u{064A}\u{062F} \u{0643}\u{0644}\u{0645}\u{0629} \u{0627}\u{0644}\u{0645}\u{0631}\u{0648}\u{0631} \u{063A}\u{064A}\u{0631} \u{0645}\u{0637}\u{0627}\u{0628}\u{0642}.", 'en' => 'Password confirmation does not match.'],
            'validation.min'                 => ['ar' => "\u{0627}\u{0644}\u{0642}\u{064A}\u{0645}\u{0629} \u{0627}\u{0644}\u{0645}\u{062F}\u{062E}\u{0644}\u{0629} \u{0623}\u{0642}\u{0644} \u{0645}\u{0646} \u{0627}\u{0644}\u{062D}\u{062F} \u{0627}\u{0644}\u{0645}\u{0637}\u{0644}\u{0648}\u{0628}.", 'en' => 'The entered value is below the allowed minimum.'],
            'validation.max'                 => ['ar' => "\u{0627}\u{0644}\u{0642}\u{064A}\u{0645}\u{0629} \u{0627}\u{0644}\u{0645}\u{062F}\u{062E}\u{0644}\u{0629} \u{0623}\u{0643}\u{0628}\u{0631} \u{0645}\u{0646} \u{0627}\u{0644}\u{062D}\u{062F} \u{0627}\u{0644}\u{0645}\u{0633}\u{0645}\u{0648}\u{062D}.", 'en' => 'The entered value exceeds the allowed maximum.'],
            'validation.size'                => ['ar' => "\u{0627}\u{0644}\u{0642}\u{064A}\u{0645}\u{0629} \u{0627}\u{0644}\u{0645}\u{062F}\u{062E}\u{0644}\u{0629} \u{0644}\u{0627} \u{062A}\u{0637}\u{0627}\u{0628}\u{0642} \u{0627}\u{0644}\u{062D}\u{062C}\u{0645} \u{0627}\u{0644}\u{0645}\u{0637}\u{0644}\u{0648}\u{0628}.", 'en' => 'The entered value does not match the required size.'],
            'validation.string'              => ['ar' => "\u{064A}\u{062C}\u{0628} \u{0623}\u{0646} \u{062A}\u{0643}\u{0648}\u{0646} \u{0627}\u{0644}\u{0642}\u{064A}\u{0645}\u{0629} \u{0646}\u{0635}\u{0627}.", 'en' => 'The value must be text.'],
            'validation.numeric'             => ['ar' => "\u{064A}\u{062C}\u{0628} \u{0623}\u{0646} \u{062A}\u{0643}\u{0648}\u{0646} \u{0627}\u{0644}\u{0642}\u{064A}\u{0645}\u{0629} \u{0631}\u{0642}\u{0645}\u{0627}.", 'en' => 'The value must be a number.'],
            'validation.integer'             => ['ar' => "\u{064A}\u{062C}\u{0628} \u{0623}\u{0646} \u{062A}\u{0643}\u{0648}\u{0646} \u{0627}\u{0644}\u{0642}\u{064A}\u{0645}\u{0629} \u{0631}\u{0642}\u{0645}\u{0627} \u{0635}\u{062D}\u{064A}\u{062D}\u{0627}.", 'en' => 'The value must be an integer.'],
            'validation.boolean'             => ['ar' => "\u{0627}\u{0644}\u{0642}\u{064A}\u{0645}\u{0629} \u{0627}\u{0644}\u{0645}\u{062D}\u{062F}\u{062F}\u{0629} \u{063A}\u{064A}\u{0631} \u{0635}\u{062D}\u{064A}\u{062D}\u{0629}.", 'en' => 'The selected value is invalid.'],
            'validation.array'               => ['ar' => "\u{0627}\u{0644}\u{0642}\u{064A}\u{0645}\u{0629} \u{0627}\u{0644}\u{0645}\u{062D}\u{062F}\u{062F}\u{0629} \u{063A}\u{064A}\u{0631} \u{0635}\u{062D}\u{064A}\u{062D}\u{0629}.", 'en' => 'The selected value is invalid.'],
            'validation.date'                => ['ar' => "\u{064A}\u{0631}\u{062C}\u{0649} \u{0625}\u{062F}\u{062E}\u{0627}\u{0644} \u{062A}\u{0627}\u{0631}\u{064A}\u{062E} \u{0635}\u{062D}\u{064A}\u{062D}.", 'en' => 'Please enter a valid date.'],
            'validation.before'              => ['ar' => "\u{0627}\u{0644}\u{062A}\u{0627}\u{0631}\u{064A}\u{062E} \u{0627}\u{0644}\u{0645}\u{062F}\u{062E}\u{0644} \u{063A}\u{064A}\u{0631} \u{0635}\u{062D}\u{064A}\u{062D}.", 'en' => 'The entered date is invalid.'],
            'validation.after'               => ['ar' => "\u{0627}\u{0644}\u{062A}\u{0627}\u{0631}\u{064A}\u{062E} \u{0627}\u{0644}\u{0645}\u{062F}\u{062E}\u{0644} \u{063A}\u{064A}\u{0631} \u{0635}\u{062D}\u{064A}\u{062D}.", 'en' => 'The entered date is invalid.'],
            'validation.after_or_equal'      => ['ar' => "\u{0627}\u{0644}\u{062A}\u{0627}\u{0631}\u{064A}\u{062E} \u{0627}\u{0644}\u{0645}\u{062F}\u{062E}\u{0644} \u{063A}\u{064A}\u{0631} \u{0635}\u{062D}\u{064A}\u{062D}.", 'en' => 'The entered date is invalid.'],
            'validation.regex'               => ['ar' => "\u{0635}\u{064A}\u{063A}\u{0629} \u{0627}\u{0644}\u{0642}\u{064A}\u{0645}\u{0629} \u{0627}\u{0644}\u{0645}\u{062F}\u{062E}\u{0644}\u{0629} \u{063A}\u{064A}\u{0631} \u{0635}\u{062D}\u{064A}\u{062D}\u{0629}.", 'en' => 'The entered value format is invalid.'],
            'validation.mimes'               => ['ar' => "\u{0646}\u{0648}\u{0639} \u{0627}\u{0644}\u{0645}\u{0644}\u{0641} \u{063A}\u{064A}\u{0631} \u{0645}\u{0633}\u{0645}\u{0648}\u{062D}.", 'en' => 'The file type is not allowed.'],
            'validation.image'               => ['ar' => "\u{064A}\u{0631}\u{062C}\u{0649} \u{0631}\u{0641}\u{0639} \u{0635}\u{0648}\u{0631}\u{0629} \u{0635}\u{062D}\u{064A}\u{062D}\u{0629}.", 'en' => 'Please upload a valid image.'],
            'validation.file'                => ['ar' => "\u{064A}\u{0631}\u{062C}\u{0649} \u{0631}\u{0641}\u{0639} \u{0645}\u{0644}\u{0641} \u{0635}\u{062D}\u{064A}\u{062D}.", 'en' => 'Please upload a valid file.'],
            'validation.uploaded'            => ['ar' => "\u{062A}\u{0639}\u{0630}\u{0631} \u{0631}\u{0641}\u{0639} \u{0627}\u{0644}\u{0645}\u{0644}\u{0641}. \u{062A}\u{0623}\u{0643}\u{062F} \u{0645}\u{0646} \u{0646}\u{0648}\u{0639} \u{0627}\u{0644}\u{0645}\u{0644}\u{0641} \u{0648}\u{062D}\u{062C}\u{0645}\u{0647} \u{062B}\u{0645} \u{0623}\u{0639}\u{062F} \u{0627}\u{0644}\u{0645}\u{062D}\u{0627}\u{0648}\u{0644}\u{0629}.", 'en' => 'The file could not be uploaded. Please check the file type and size, then try again.'],
        ];
    }

    private static function localized(array $texts): string
    {
        $locale = function_exists('app') ? (string) app()->getLocale() : 'en';

        return substr($locale, 0, 2) === 'ar'
            ? ($texts['ar'] ?? $texts['en'] ?? '')
            : ($texts['en'] ?? $texts['ar'] ?? '');
    }

    public static function hideInline(?string $key = null): bool
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
            $mapped = $map[$original];
            $value = is_array($mapped) ? self::localized($mapped) : $mapped;
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

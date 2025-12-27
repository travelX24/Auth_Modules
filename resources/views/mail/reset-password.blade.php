@php
    $locale = app()->getLocale();
    $rtl = in_array($locale, ['ar', 'ar_YE', 'ar-SA', 'ar-EG']);

    // مترجم يعتمد على package tr() + استبدال المتغيرات :key
    $t = function (string $text, array $replace = []) {
        $value = function_exists('tr') ? tr($text) : $text;

        foreach ($replace as $k => $v) {
            $value = str_replace(':' . $k, (string) $v, $value);
        }

        return $value;
    };
@endphp

@component('mail::message')
<div dir="{{ $rtl ? 'rtl' : 'ltr' }}" style="text-align: {{ $rtl ? 'right' : 'left' }}">

# {{ $t('Hello :name,', ['name' => $name]) }}

{{ $t('You are receiving this email because we received a password reset request for your account.') }}

@component('mail::button', ['url' => $url])
{{ $t('Reset password') }}
@endcomponent

{{ $t('This password reset link will expire in :minutes minutes.', ['minutes' => $minutes]) }}

{{ $t('If you did not request a password reset, no further action is required.') }}

{{ $t('Regards, :app', ['app' => $appName]) }}

</div>
@endcomponent

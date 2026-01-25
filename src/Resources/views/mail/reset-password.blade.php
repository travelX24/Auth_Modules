@php
    $locale = app()->getLocale();
    $rtl = in_array($locale, ['ar', 'ar_YE', 'ar-SA', 'ar-EG']);

    // مترجم يعتمد على package tr()
    $t = function (string $text) {
        return function_exists('tr') ? tr($text) : $text;
    };
@endphp

@component('mail::message')
<div dir="{{ $rtl ? 'rtl' : 'ltr' }}" style="text-align: {{ $rtl ? 'right' : 'left' }}; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

# {{ $t('Hello') }} {{ $name }},

{{ $t('You can now set a new password for your account in') }} **{{ $companyName }}**.

<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin: 20px 0;">
    <p style="margin: 0; color: #64748b; font-size: 14px;">{{ $t('Login Email:') }}</p>
    <p style="margin: 0; color: #0f172a; font-weight: bold; font-size: 16px;">{{ $email }}</p>
</div>

{{ $t('Please click the button below to proceed with setting your password:') }}

@component('mail::button', ['url' => $url])
{{ $t('Set Password') }}
@endcomponent

<p style="font-size: 13px; color: #64748b;">
    <i class="fas fa-info-circle"></i> {{ $t('This link will expire in') }} {{ $minutes }} {{ $t('minutes.') }}
</p>

{{ $t('If you did not request this, you can safely ignore this email.') }}

{{ $t('Best Regards,') }}<br>
**{{ $companyName }}** <br>
<span style="font-size: 12px; color: #94a3b8;">{{ $t('Powered by') }} {{ $appName }}</span>

</div>
@endcomponent

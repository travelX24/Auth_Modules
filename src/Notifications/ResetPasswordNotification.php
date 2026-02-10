<?php

namespace Athka\AuthKit\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token,
        public string $lang = 'en',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // name prefix من config/authkit.php (مثال: authkit.)
        $as = (string) config('authkit.routes.as', 'authkit.');
        $as = $as === '' ? '' : rtrim($as, '.') . '.';

        // جرّب أسماء محتملة (عشان ما تتعب مستقبلاً لو تغيّر as)
        $candidates = [
            $as . 'password.reset',      // غالباً: authkit.password.reset
            'authkit.password.reset',    // fallback
            'password.reset',            // fallback لو عندك auth الافتراضي
        ];

        $routeName = null;
        foreach ($candidates as $name) {
            if ($name && Route::has($name)) {
                $routeName = $name;
                break;
            }
        }

        if (!$routeName) {
            throw new \RuntimeException(
                'Reset password route not found. Tried: ' . implode(', ', $candidates)
            );
        }

        $url = route($routeName, ['token' => $this->token], true);
        $url .= (strpos($url, '?') === false ? '?' : '&') . 'email=' . urlencode($notifiable->getEmailForPasswordReset());

        $minutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');
        $appName = (string) config('app.name', 'Athka HR');

        // Fetch Company Name
        $companyName = $appName;
        if (!empty($notifiable->saas_company_id)) {
             $saasCompanyClass = \Athka\Saas\Models\SaasCompany::class;
             if (class_exists($saasCompanyClass)) {
                 $company = $saasCompanyClass::find($notifiable->saas_company_id);
                 if ($company) {
                     $companyName = app()->getLocale() == 'ar' ? ($company->legal_name_ar ?? $company->legal_name_en) : ($company->legal_name_en ?? $company->legal_name_ar);
                 }
             }
        }

        // Use Employee Name if available
        $displayName = $notifiable->name;
        if ($notifiable->employee) {
            $displayName = app()->getLocale() == 'ar' ? ($notifiable->employee->name_ar ?? $notifiable->employee->name_en) : ($notifiable->employee->name_en ?? $notifiable->employee->name_ar);
        }

        $prev = App::getLocale();
        App::setLocale($this->lang);

        try {
               $email = $notifiable->getEmailForPasswordReset();

                $custom = config('authkit.password_reset_url');
                if ($custom) {
                    // مثال: myapp://reset-password?token={token}&email={email}
                    $url = str_replace(
                        ['{token}', '{email}'],
                        [$this->token, urlencode($email)],
                        (string) $custom
                    );
                } else {
                    // fallback: رابط الويب الحالي
                    $url = url("/reset-password/{$this->token}?email=" . urlencode($email));
                }

            return (new MailMessage)
                ->subject('Reset Password for ' . $companyName)
                ->view(config('authkit.mail.reset', 'authkit::mail.reset-password'), [
                    'name'        => $displayName,
                    'email'       => $notifiable->email,
                    'url'         => $url,
                    'minutes'     => $minutes,
                    'appName'     => $appName,
                    'companyName' => $companyName,
                ]);
        } finally {
            App::setLocale($prev);
        }
    }
}

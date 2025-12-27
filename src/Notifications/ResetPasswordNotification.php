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

        $url = url(route($routeName, [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $minutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');
        $appName = (string) config('app.name');

        $prev = App::getLocale();
        App::setLocale($this->lang);

        try {
            return (new MailMessage)
                ->subject(__('Reset password - :app', ['app' => $appName]))
                ->view(config('authkit.mail.reset', 'authkit::mail.reset-password'), [
                    'name'    => $notifiable->name ?? '',
                    'url'     => $url,
                    'minutes' => $minutes,
                    'appName' => $appName,
                ]);
        } finally {
            App::setLocale($prev);
        }
    }
}

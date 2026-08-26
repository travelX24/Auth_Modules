<?php

namespace Athka\AuthKit\Listeners;

use Athka\AuthKit\Support\AuditLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Events\Dispatcher;

class AuditAuthEventsSubscriber
{
    /**
     * Handle Login event.
     */
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        AuditLogger::logIam('login', $user instanceof \Illuminate\Database\Eloquent\Model ? $user : null);
    }

    /**
     * Handle Login Failed event.
     */
    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? 'unknown';
        AuditLogger::logIam('login_failed', null, [], ['attempted_email' => $email]);
    }

    /**
     * Handle Logout event.
     */
    public function handleLogout(Logout $event): void
    {
        $user = $event->user;
        AuditLogger::logIam('logout', $user instanceof \Illuminate\Database\Eloquent\Model ? $user : null);
    }

    /**
     * Handle PasswordReset event.
     */
    public function handlePasswordReset(PasswordReset $event): void
    {
        $user = $event->user;
        AuditLogger::logIam('password_reset', $user instanceof \Illuminate\Database\Eloquent\Model ? $user : null);
    }

    /**
     * Register listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Failed::class => 'handleFailed',
            Logout::class => 'handleLogout',
            PasswordReset::class => 'handlePasswordReset',
        ];
    }
}

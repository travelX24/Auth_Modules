<?php

namespace Athka\AuthKit\Traits;

use Athka\AuthKit\Notifications\ResetPasswordNotification;

trait SendsAuthKitPasswordReset
{
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token, app()->getLocale()));
    }
}

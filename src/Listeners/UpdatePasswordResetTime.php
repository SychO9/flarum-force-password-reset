<?php

namespace SychO\ForcePasswordReset\Listeners;

use Carbon\Carbon;
use Flarum\User\Event\PasswordChanged;

class UpdatePasswordResetTime
{
    public function handle(PasswordChanged $event): void
    {
        $user = $event->user;

        if ($user->required_password_reset_at !== null && (! $user->password_reset_at || $user->password_reset_at->lt($user->required_password_reset_at))) {
            $user->password_reset_at = Carbon::now();
            $user->save();
        }
    }
}

<?php

/*
 * This file is part of sycho/flarum-force-password-reset.
 *
 * Copyright (c) 2023 Sami.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SychO\ForcePasswordReset;

use Flarum\Extend;
use Flarum\User\Event\PasswordChanged;
use Flarum\User\User;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Model(User::class))
        ->cast('required_password_reset_at', 'datetime')
        ->cast('password_reset_at', 'datetime'),

    (new Extend\Event())
        ->listen(PasswordChanged::class, Listeners\UpdatePasswordResetTime::class),

    (new Extend\Routes('api'))
        ->post('/force-password-reset', 'sycho-force-password-reset.requireReset', Api\Controller\ForcePasswordResetController::class),
    // Override flarum routes to add current password check
    (new Extend\Routes('forum'))
        ->get('/force-reset/{token}', 'sycho-force-password-reset.resetPassword', Forum\Controller\ResetPasswordController::class)
        ->post('/force-reset', 'sycho-force-password-reset.savePassword', Forum\Controller\SavePasswordController::class),
    (new Extend\View())
        ->namespace('sycho-force-password-reset', __DIR__.'/views'),

    (new Extend\Middleware('forum'))
        ->add(RedirectToPasswordResetMiddleware::class),
    (new Extend\Middleware('admin'))
        ->add(RedirectToPasswordResetMiddleware::class),
];

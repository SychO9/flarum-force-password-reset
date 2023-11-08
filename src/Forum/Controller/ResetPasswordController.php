<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace SychO\ForcePasswordReset\Forum\Controller;

use DateTime;
use Flarum\Http\Controller\AbstractHtmlController;
use Flarum\Http\RequestUtil;
use Flarum\User\Exception\InvalidConfirmationTokenException;
use Flarum\User\Exception\NotAuthenticatedException;
use Flarum\User\PasswordToken;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @override \Flarum\Forum\Controller\ResetPasswordController
 */
class ResetPasswordController extends AbstractHtmlController
{
    /**
     * @var Factory
     */
    protected $view;

    /**
     * @param Factory $view
     */
    public function __construct(Factory $view)
    {
        $this->view = $view;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     * @throws \Flarum\User\Exception\InvalidConfirmationTokenException
     */
    public function render(Request $request)
    {
        $token = Arr::get($request->getQueryParams(), 'token');

        /** @var PasswordToken $token */
        $token = PasswordToken::findOrFail($token);

        $actor = RequestUtil::getActor($request);
        $actor->assertRegistered();

        if ($actor->id !== $token->user_id) {
            throw new NotAuthenticatedException;
        }

        if ($token->created_at < new DateTime('-1 day')) {
            throw new InvalidConfirmationTokenException;
        }

        return $this->view->make('sycho-force-password-reset::reset-password')
            ->with('passwordToken', $token->token)
            ->with('csrfToken', $request->getAttribute('session')->token());
    }
}

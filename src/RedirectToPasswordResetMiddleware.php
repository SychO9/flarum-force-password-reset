<?php

namespace SychO\ForcePasswordReset;

use Flarum\Http\RequestUtil;
use Flarum\Http\UrlGenerator;
use Flarum\User\PasswordToken;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RedirectToPasswordResetMiddleware implements MiddlewareInterface
{
    /**
     * @var UrlGenerator
     */
    protected $url;

    public function __construct(UrlGenerator $url)
    {
        $this->url = $url;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getAttribute('routeName'), ['sycho-force-password-reset.resetPassword', 'sycho-force-password-reset.savePassword'], true)) {
            return $handler->handle($request);
        }

        $actor = RequestUtil::getActor($request);

        if (! $actor->isGuest() && $actor->required_password_reset_at !== null && (! $actor->password_reset_at || $actor->password_reset_at->lt($actor->required_password_reset_at))) {
            $passwordToken = $actor->passwordTokens()->first() ?: PasswordToken::generate($actor->id);
            ! $passwordToken->exists && $passwordToken->save();

            return new RedirectResponse($this->url->to('forum')->route('sycho-force-password-reset.resetPassword', ['token' => $passwordToken->token]));
        }

        return $handler->handle($request);
    }
}

<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace SychO\ForcePasswordReset\Forum\Controller;

use Flarum\Foundation\DispatchEventsTrait;
use Flarum\Http\SessionAccessToken;
use Flarum\Http\SessionAuthenticator;
use Flarum\Http\UrlGenerator;
use Flarum\User\PasswordToken;
use Flarum\User\UserValidator;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @override \Flarum\Forum\Controller\SavePasswordController
 */
class SavePasswordController implements RequestHandlerInterface
{
    use DispatchEventsTrait;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @var \Flarum\User\UserValidator
     */
    protected $validator;

    /**
     * @var SessionAuthenticator
     */
    protected $authenticator;

    /**
     * @var Factory
     */
    protected $validatorFactory;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(UrlGenerator $url, SessionAuthenticator $authenticator, UserValidator $validator, Factory $validatorFactory, Dispatcher $events, TranslatorInterface $translator)
    {
        $this->url = $url;
        $this->authenticator = $authenticator;
        $this->validator = $validator;
        $this->validatorFactory = $validatorFactory;
        $this->events = $events;
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function handle(Request $request): ResponseInterface
    {
        $input = $request->getParsedBody();

        /** @var PasswordToken $token */
        $token = PasswordToken::findOrFail(Arr::get($input, 'passwordToken'));

        $password = Arr::get($input, 'password');

        try {
            // todo: probably shouldn't use the user validator for this,
            // passwords should be validated separately
            $this->validator->assertValid(compact('password'));

            $validator = $this->validatorFactory->make($input, ['password' => 'required|confirmed']);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        } catch (ValidationException $e) {
            // @todo: must return a 422 instead, look into renderable exceptions.
            return $this->displayWithErrors($request, $token, $e->errors());
        }

        if ($token->user->checkPassword($password)) {
            return $this->displayWithErrors($request, $token, [
                'password' => $this->translator->trans('sycho-force-password-reset.forum.new_password_must_be_different')
            ]);
        }

        $token->user->changePassword($password);
        $token->user->save();

        $this->dispatchEventsFor($token->user);

        $session = $request->getAttribute('session');
        $accessToken = SessionAccessToken::generate($token->user->id);
        $this->authenticator->logIn($session, $accessToken);

        return new RedirectResponse($this->url->to('forum')->base());
    }

    private function displayWithErrors(ServerRequestInterface $request, PasswordToken $token, array $errors): RedirectResponse
    {
        $request->getAttribute('session')->put('errors', new MessageBag($errors));

        return new RedirectResponse($this->url->to('forum')->route('sycho-force-password-reset.resetPassword', ['token' => $token->token]));
    }
}

<?php

namespace SychO\ForcePasswordReset\Api\Controller;

use Carbon\Carbon;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ForcePasswordResetController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);

        $actor->assertAdmin();

        User::query()
            ->where('id', '!=', $actor->id)
            ->update([
                'required_password_reset_at' => Carbon::now(),
                'password_reset_at' => null,
            ]);

        return new Response\EmptyResponse(204);
    }
}

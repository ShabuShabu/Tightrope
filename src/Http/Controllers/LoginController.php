<?php

namespace ShabuShabu\Tightrope\Http\Controllers;

use Carbon\CarbonInterface;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\{JsonResponse, Response};
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Cookie;
use function ShabuShabu\Tightrope\{to_camel_case};

class LoginController
{
    /**
     * Log a user in
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     * @throws \JsonException
     */
    public function __invoke(ServerRequestInterface $request): JsonResponse
    {
        /** @var \Illuminate\Http\Response $response */
        $response = app(AccessTokenController::class)->issueToken($request);

        $content = collect(
            json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR)
        );

        abort_if($content->isEmpty(), Response::HTTP_UNAUTHORIZED);

        event(new Login('api', auth()->user(), false));

        if ($this->shouldAddCookie($request)) {
            return response()->json(
                to_camel_case(
                    $content->only('access_token', 'expires_in', 'token_type')->toArray()
                )
            )->cookie(
                $this->refreshCookie($content->get('refresh_token'))
            );
        }

        return response()->json(
            to_camel_case($content->only([
                'access_token',
                'refresh_token',
                'expires_in',
                'token_type',
            ])->toArray())
        );
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return bool
     */
    protected function shouldAddCookie(ServerRequestInterface $request): bool
    {
        return ($request->getHeader('X-Cookie-Auth')[0] ?? '0') === '1';
    }

    /**
     * Create a secure authentication cookie
     *
     * @param string $refreshToken
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function refreshCookie(string $refreshToken): Cookie
    {
        return new Cookie(
            config('tightrope.refresh_cookie_name'),
            $refreshToken,
            $this->tokenValidity(),
            config('session.path'),
            config('session.domain'),
            true,
            true,
            false,
            Cookie::SAMESITE_STRICT
        );
    }

    /**
     * @return \Carbon\CarbonInterface
     */
    protected function tokenValidity(): CarbonInterface
    {
        return now()->addMinutes(
            config('tightrope.refresh_token_validity')
        );
    }
}

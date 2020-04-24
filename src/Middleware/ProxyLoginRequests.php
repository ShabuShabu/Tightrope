<?php

namespace ShabuShabu\Tightrope\Middleware;

use Closure;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Client;

class ProxyLoginRequests
{
    protected Request $request;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(Request $request, Closure $next)
    {
        $this->request = $request;

        if ($this->isRefreshing()) {
            return $this->handleRefreshToken($next);
        }

        return $this->handleAccessToken($next);
    }

    /**
     * @param \Closure $next
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function handleAccessToken(Closure $next)
    {
        if (! $this->actAsProxy()) {
            return $next($this->request);
        }

        if (! auth(config('tightrope.auth_guard'))->once($this->request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'login' => 'Your login credentials are incorrect',
            ]);
        }

        if ($client = $this->getClient()) {
            $fields = [
                'username'      => $this->request->input('email'),
                'grant_type'    => 'password',
                'client_id'     => $client->getAttribute('id'),
                'client_secret' => $client->getAttribute('secret'),
            ];

            foreach ($fields as $key => $value) {
                $this->setJson($key, $value);
            }

            if (! $this->request->has('scope')) {
                $this->setJson('scope', '*');
            }
        }

        return $next($this->request);
    }

    /**
     * @param \Closure $next
     * @return mixed
     */
    protected function handleRefreshToken(Closure $next)
    {
        if ($client = $this->getClient()) {
            $this->setJson('client_id', $client->getAttribute('id'));
            $this->setJson('client_secret', $client->getAttribute('secret'));

            $cookieName = config('tightrope.refresh_cookie_name');

            if ($this->request->hasCookie($cookieName)) {
                $this->setJson('refresh_token', $this->request->cookie($cookieName));
            }

            if (! $this->request->has('scope')) {
                $this->setJson('scope', '*');
            }
        }

        return $next($this->request);
    }

    /**
     * @return bool
     */
    protected function isRefreshing(): bool
    {
        return $this->request->has('grant_type') && $this->request->input('grant_type') === 'refresh_token';
    }

    /**
     * @return bool
     */
    protected function actAsProxy(): bool
    {
        return ! $this->request->has('client_id', 'client_secret') && $this->request->has('password');
    }

    /**
     * Get the official password client
     *
     * @return \Laravel\Passport\Client
     */
    protected function getClient(): ?Client
    {
        /** @var Client $client */
        $client = Client::query()
                        ->where('name', config('tightrope.client_name'))
                        ->where('password_client', true)
                        ->oldest()
                        ->first();

        return $client && ! $client->getAttribute('revoked') ? $client : null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    protected function setJson(string $key, $value): void
    {
        optional($this->request->json())->set($key, $value);
    }
}

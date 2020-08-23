<?php

namespace ShabuShabu\Tightrope\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $client = $this->getClient()) {
            return $next($request);
        }

        $this->request = $request;

        if ($this->isRefreshing()) {
            $this->handleRefreshToken($client);
        } elseif ($this->actAsProxy()) {
            $this->handleAccessToken($client);
        }

        if (! $this->request->has('scope')) {
            $this->setJson('scope', '');
        }

        return $next($this->request);
    }

    /**
     * @param \Laravel\Passport\Client $client
     * @return void
     */
    protected function handleAccessToken(Client $client): void
    {
        $usernameField = config('tightrope.username_field');

        $fields = [
            'username'      => $this->request->input($usernameField),
            'grant_type'    => 'password',
            'client_id'     => $client->getAttribute('id'),
            'client_secret' => $client->getAttribute('secret'),
        ];

        foreach ($fields as $key => $value) {
            $this->setJson($key, $value);
        }

        optional($this->request->json())->remove($usernameField);
    }

    /**
     * @param \Laravel\Passport\Client $client
     * @return void
     */
    protected function handleRefreshToken(Client $client): void
    {
        $fields = [
            'client_id'     => $client->getAttribute('id'),
            'client_secret' => $client->getAttribute('secret'),
        ];

        foreach ($fields as $key => $value) {
            $this->setJson($key, $value);
        }

        $cookieName = config('tightrope.refresh_cookie_name');

        if ($this->request->hasCookie($cookieName)) {
            $this->setJson('refresh_token', $this->request->cookie($cookieName));
        }
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
     * @return \Laravel\Passport\Client|null|mixed
     */
    protected function getClient()
    {
        return Client::query()
                     ->where('name', config('tightrope.client_name'))
                     ->where('password_client', true)
                     ->where('revoked', false)
                     ->oldest()
                     ->first();
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

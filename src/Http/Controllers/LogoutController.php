<?php

namespace ShabuShabu\Tightrope\Http\Controllers;

use Illuminate\Auth\Events\Logout;
use Illuminate\Http\{Request, Response};
use ShabuShabu\Tightrope\Tightrope;

class LogoutController
{
    /**
     * Log a user out
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $response = Tightrope::logUserOut($request);

        event(new Logout('api', $request->user()));

        cookie()->queue(
            cookie()->forget(config('tightrope.refresh_cookie_name'))
        );

        return $response;
    }
}

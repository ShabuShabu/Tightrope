<?php

namespace ShabuShabu\Tightrope\Controllers;

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
        $response = Tightrope::logoutUser($request);

        cookie()->queue(
            cookie()->forget(config('tightrope.refresh_cookie_name'))
        );

        return $response;
    }
}

<?php

namespace ShabuShabu\Tightrope\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\{Request, Response};
use ShabuShabu\Tightrope\Tightrope;

class RegisterController
{
    /**
     * Register a user
     *
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function __invoke(Request $request): Response
    {
        if ($user = Tightrope::registerUser($request)) {
            event(new Registered($user));

            return response('Registered', Response::HTTP_OK);
        }

        return response('Registration unsuccessful', Response::HTTP_BAD_REQUEST);
    }
}

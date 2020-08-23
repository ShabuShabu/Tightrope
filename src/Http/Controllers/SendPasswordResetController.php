<?php

namespace ShabuShabu\Tightrope\Http\Controllers;

use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\Password;

class SendPasswordResetController
{
    /**
     * Send a reset password email
     *
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $formRequest = config('tightrope.requests.send_password');
        $request     = $formRequest::createFromBase($request);

        $request->validateResolved();

        $response = Password::broker()->sendResetLink(
            $request->only('email')
        );

        if ($response === Password::RESET_LINK_SENT) {
            return response($response, Response::HTTP_OK);
        }

        return response($response, Response::HTTP_BAD_REQUEST);
    }
}

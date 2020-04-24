<?php

namespace ShabuShabu\Tightrope\Controllers;

use Illuminate\Http\{Request, Response};

class ResendVerificationEmailController
{
    /**
     * Resend the email verification notification.
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response('Already verified', Response::HTTP_NOT_MODIFIED);
        }

        $user->sendEmailVerificationNotification();

        return response('Resent', Response::HTTP_OK);
    }
}

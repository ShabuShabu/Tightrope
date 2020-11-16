<?php

namespace ShabuShabu\Tightrope\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\{Request, Response};

class VerifyEmailController
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $userId
     * @return Response|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, string $userId)
    {
        $userClass = config('tightrope.user_model');
        $user      = $userClass::findOrFail($userId);

        if ($user->hasVerifiedEmail()) {
            return response('Already verified', Response::HTTP_NOT_MODIFIED);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        if ($redirect = config('tightrope.redirect_after_verify')) {
            return response()->redirectTo($redirect);
        }

        return response('Verified', Response::HTTP_OK);
    }
}

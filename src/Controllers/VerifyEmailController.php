<?php

namespace ShabuShabu\Tightrope\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\{Request, Response};

class VerifyEmailController
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        if ($request->route('id') !== $user->getKey()) {
            throw new AuthorizationException('You cannot verify another user');
        }

        if ($user->hasVerifiedEmail()) {
            return response('Already verified', Response::HTTP_NOT_MODIFIED);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response('Verified', Response::HTTP_OK);
    }
}

<?php

namespace ShabuShabu\Tightrope\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use function ShabuShabu\Tightrope\hash_value;

class ResetPasswordController
{
    /**
     * Reset a travellers password
     *
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $formRequest = config('tightrope.requests.reset_password');
        $request     = $formRequest::createFromBase($request);

        $request->validateResolved();

        $response = Password::broker()->reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            fn($user, $password) => $user->forceFill([
                'password'       => hash_value($password),
                'remember_token' => Str::random(60),
            ])->save()
        );

        if ($response === Password::PASSWORD_RESET) {
            event(new PasswordReset(auth()->user()));

            return response($response, Response::HTTP_OK);
        }

        return response($response, Response::HTTP_BAD_REQUEST);
    }
}

<?php

namespace ShabuShabu\Tightrope;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\{Request, Response};
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use ShabuShabu\Tightrope\Http\Controllers\{LoginController,
    LogoutController,
    RegisterController,
    ResendVerificationEmailController,
    ResetPasswordController,
    SendPasswordResetController,
    VerifyEmailController
};

class Tightrope
{
    protected static Closure $logUserOutCallback;

    protected static Closure $registerUserCallback;

    /**
     * @param null  $callback
     * @param array $options
     */
    public static function routes($callback = null, array $options = []): void
    {
        $callback = $callback ?: static function(Router $router) {
            $router->post('register', RegisterController::class)
                   ->name('register');

            $router->post('login', LoginController::class)
                   ->middleware('proxy.login')
                   ->name('login');

            $router->post('password/request', SendPasswordResetController::class)
                   ->name('password.request');

            $router->post('password/reset', ResetPasswordController::class)
                   ->name('password.reset');

            $router->middleware('auth:api')->group(static function(Router $router) {
                $router->get('email/verify/{id}', VerifyEmailController::class)
                       ->middleware('signed')
                       ->name('verification.verify');

                $router->post('email/resend', ResendVerificationEmailController::class)
                       ->name('verification.resend');

                $router->post('logout', LogoutController::class)
                       ->name('logout');
            });
        };

        Route::group($options, fn(Router $router) => $callback($router));
    }

    /**
     * @param Closure $callback
     */
    public static function logUserOutUsing(Closure $callback): void
    {
        static::$logUserOutCallback = $callback;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public static function logUserOut(Request $request): Response
    {
        if (static::$logUserOutCallback) {
            return call_user_func(static::$logUserOutCallback, $request);
        }

        return response('Logged out', Response::HTTP_OK);
    }

    /**
     * @param Closure $callback
     */
    public static function registerUserUsing(Closure $callback): void
    {
        static::$registerUserCallback = $callback;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return Authenticatable
     */
    public static function registerUser(Request $request): Authenticatable
    {
        if (static::$registerUserCallback) {
            return call_user_func(static::$registerUserCallback, $request);
        }

        throw new RuntimeException('Tightrope register callback was not registered.');
    }
}

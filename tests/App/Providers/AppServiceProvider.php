<?php

namespace ShabuShabu\Tightrope\Tests\App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use ShabuShabu\Tightrope\Tightrope;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Passport::routes();
        Passport::ignoreMigrations();
        Passport::loadKeysFrom(dirname(__DIR__) . '/keys');

        Tightrope::routes();

        Tightrope::registerUserUsing(static function (Request $request) {
            // do your registration thing here, like
            // validation, saving the user, etc
        });

        Tightrope::logUserOutUsing(static function (Request $request) {
            // do something with $request->user()
        });
    }
}

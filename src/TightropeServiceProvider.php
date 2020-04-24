<?php

namespace ShabuShabu\Tightrope;

use Illuminate\Support\ServiceProvider;
use ShabuShabu\Tightrope\Middleware\ProxyLoginRequests;

class TightropeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/tightrope.php' => config_path('tightrope.php'),
            ], 'config');
        }

        $this->app['router']->aliasMiddleware('proxy.login', ProxyLoginRequests::class);
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tightrope.php', 'tightrope');
    }
}

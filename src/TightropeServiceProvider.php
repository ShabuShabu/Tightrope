<?php

namespace ShabuShabu\Tightrope;

use Illuminate\Support\ServiceProvider;

class TightropeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/tightrope.php' => config_path('tightrope.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tightrope.php', 'tightrope');
    }
}

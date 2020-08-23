<?php

namespace ShabuShabu\Tightrope\Tests\Support;

use Illuminate\Routing\Router;
use Laravel\Passport\PassportServiceProvider;
use ShabuShabu\Tightrope\Tests\App\Providers\AppServiceProvider;
use ShabuShabu\Tightrope\TightropeServiceProvider;

trait AppSetup
{
    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(dirname(__DIR__) . '/App/migrations');
        $this->withFactories(dirname(__DIR__) . '/App/factories');

        $this->artisan('passport:client', [
            '--password' => true,
            '--name'     => config('tightrope.client_name'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvironmentSetUp($app): void
    {
        $this->setupRouting($app['router']);

        $app['config']->set('auth.guards.api.driver', 'passport');
        $app['config']->set('hashing.driver', 'bcrypt');
    }

    /**
     * @param \Illuminate\Routing\Router $router
     */
    protected function setupRouting(Router $router): void
    {
        $router->get('me', fn() => response('OK'))
               ->middleware('auth:api');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPackageProviders($app): array
    {
        return [
            AppServiceProvider::class,
            TightropeServiceProvider::class,
            PassportServiceProvider::class,
        ];
    }
}

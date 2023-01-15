<?php

namespace KieranFYI\Services\Core\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use KieranFYI\Services\Core\Http\Middleware\Authenticate;
use KieranFYI\Services\Core\Models\Service;

class ServicesCorePackageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $root = __DIR__ . '/../..';

        $this->loadRoutesFrom($root . '/routes/web.php');
        $this->loadMigrationsFrom($root . '/database/migrations');

        $router->aliasMiddleware('services.auth', Authenticate::class);

        config([
            'auth.guards.services' => [
                'driver' => 'token',
                'provider' => 'services',
                'hash' => false,
                'storage_key' => 'key',
            ],
            'auth.providers.services' => [
                'driver' => 'eloquent',
                'model' => Service::class,
            ],
        ]);
    }
}

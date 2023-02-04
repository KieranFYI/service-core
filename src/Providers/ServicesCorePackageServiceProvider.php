<?php

namespace KieranFYI\Services\Core\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use KieranFYI\Services\Core\Console\Commands\ServiceGenerate;
use KieranFYI\Services\Core\Console\Commands\ServiceProvides;
use KieranFYI\Services\Core\Console\Commands\ServiceRegister;
use KieranFYI\Services\Core\Http\Middleware\Authenticate;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Models\ServiceModel;
use KieranFYI\Services\Core\Models\ServiceModelType;

class ServicesCorePackageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        Relation::morphMap([
            'service' => Service::class,
            'serviceModel' => ServiceModel::class,
            'serviceModelType' => ServiceModelType::class,
        ]);

        $root = realpath(__DIR__ . '/../..');

        $this->loadMigrationsFrom($root . '/database/migrations');
        $this->mergeConfigFrom($root . '/config/service.php', 'service');
        $this->loadRoutesFrom($root . '/routes/web.php');

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

        if ($this->app->runningInConsole()) {
            $this->commands([
                ServiceGenerate::class,
                ServiceProvides::class,
                ServiceRegister::class,
            ]);
        }
    }
}

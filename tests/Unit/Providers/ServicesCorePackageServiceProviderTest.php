<?php

namespace KieranFYI\Tests\Services\Core\Unit\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use KieranFYI\Admin\Events\RegisterAdminNavigationEvent;
use KieranFYI\Media\Listeners\RegisterAdminNavigationListener;
use KieranFYI\Services\Core\Console\Commands\ServiceGenerate;
use KieranFYI\Services\Core\Console\Commands\ServiceProvides;
use KieranFYI\Services\Core\Console\Commands\ServiceRegister;
use KieranFYI\Services\Core\Http\Middleware\Authenticate;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Models\ServiceModel;
use KieranFYI\Services\Core\Models\ServiceModelType;

class ServicesCorePackageServiceProviderTest extends \KieranFYI\Tests\Services\Core\TestCase
{

    public function testRelationMorphMap()
    {
        $this->assertEquals(Service::class, Relation::getMorphedModel('service'));
        $this->assertEquals(ServiceModel::class, Relation::getMorphedModel('serviceModel'));
        $this->assertEquals(ServiceModelType::class, Relation::getMorphedModel('serviceModelType'));
    }

    public function testLoadMigrations()
    {
        $this->markTestIncomplete();
    }

    public function testMergeConfig()
    {
        $this->assertEquals([
            'enabled' => false,
            'encrypt' => true,
            'path' => 'services',
        ], config('service'));
    }

    public function testLoadRoutes()
    {
        $this->assertTrue(Route::has('service'));
    }

    public function testAliasMiddleware()
    {
        $middleware = app(Router::class)->getMiddleware();
        $this->assertIsArray($middleware);
        $this->assertArrayHasKey('services.auth', $middleware);
        $this->assertEquals(Authenticate::class, $middleware['services.auth']);
    }

    public function testAuthGuard()
    {
        $this->assertEquals([
            'driver' => 'token',
            'provider' => 'services',
            'hash' => false,
            'storage_key' => 'key',
        ], config('auth.guards.services'));

        $this->assertEquals([
            'driver' => 'eloquent',
            'model' => Service::class,
        ], config('auth.providers.services'));
    }

    public function testCommands()
    {
        $commands = Artisan::all();
        $this->assertInstanceOf(ServiceGenerate::class, $commands['service:generate']);
        $this->assertInstanceOf(ServiceProvides::class, $commands['service:provides']);
        $this->assertInstanceOf(ServiceRegister::class, $commands['service:register']);
    }
}
<?php

namespace KieranFYI\Tests\Services\Core;

use Illuminate\Foundation\Application;
use KieranFYI\Logging\Providers\LoggingPackageServiceProvider;
use KieranFYI\Misc\Providers\MiscPackageServiceProvider;
use KieranFYI\Services\Core\Providers\ServicesCorePackageServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Load package service provider.
     *
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            MiscPackageServiceProvider::class,
            LoggingPackageServiceProvider::class,
            ServicesCorePackageServiceProvider::class,
        ];
    }
}
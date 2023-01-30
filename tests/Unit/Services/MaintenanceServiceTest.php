<?php

namespace KieranFYI\Tests\Services\Core\Unit\Services;

use Illuminate\Support\Facades\Artisan;
use KieranFYI\Services\Core\Services\MaintenanceService;
use KieranFYI\Tests\Services\Core\TestCase;

class MaintenanceServiceTest extends TestCase
{

    protected function tearDown(): void
    {
        parent::tearDown();
        Artisan::call('up');
    }

    public function testUp()
    {
        $service = MaintenanceService::create(true);
        $service->execute();
        $this->assertFalse(app()->isDownForMaintenance());
    }

    public function testDown()
    {
        $service = MaintenanceService::create(false);
        $service->execute();
        $this->assertTrue(app()->isDownForMaintenance());
    }
}
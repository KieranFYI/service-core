<?php

namespace KieranFYI\Tests\Services\Core\Unit\Services;

use Illuminate\Support\Collection;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Models\ServiceModelType;
use KieranFYI\Services\Core\Services\RegistrationService;
use KieranFYI\Tests\Services\Core\TestCase;

class RegistrationServiceTest extends TestCase
{

    public function testExecute()
    {
        $this->artisan('migrate')->run();

        $symmetricKey = random_bytes(32);

        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test'
        ]);
        $this->be($service);

        $response = RegistrationService::create($symmetricKey)
            ->execute();

        $this->assertInstanceOf(Collection::class, $response);
    }
    public function testExecuteWithType()
    {
        $this->artisan('migrate')->run();

        $symmetricKey = random_bytes(16);

        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test'
        ]);
        $this->be($service);

        $type = ServiceModelType::create([
            'name' => Service::class
        ]);
        $service->types()->save($type, ['accessible' => true]);

        $response = RegistrationService::create($symmetricKey)
            ->execute();

        $this->assertInstanceOf(Collection::class, $response);
        $expected = collect([Service::class]);
        $this->assertEquals($expected, $response);

    }

}
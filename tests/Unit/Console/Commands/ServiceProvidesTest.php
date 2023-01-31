<?php

namespace Console\Commands;

use Illuminate\Support\Facades\Event;
use KieranFYI\Services\Core\Events\RegisterServiceModelsEvent;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Models\ServiceModel;
use KieranFYI\Services\Core\Models\ServiceModelType;
use KieranFYI\Tests\Services\Core\TestCase;
use TypeError;

class ServiceProvidesTest extends TestCase
{

    public function testHandle()
    {
        $this->artisan('migrate');
        $this->artisan('service:provides');

        $names = ServiceModelType::get()
            ->pluck('name')
            ->toArray();
        $expected = [
            Service::class,
            ServiceModel::class,
            ServiceModelType::class
        ];
        $this->assertEquals($expected, $names);
    }

    public function testHandleInvalidEvent()
    {
        Event::listen(RegisterServiceModelsEvent::class, function () {
            return null;
        });
        $this->artisan('migrate');
        $this->expectException(TypeError::class);
        $this->artisan('service:provides');
    }

    public function testHandleInvalidModel()
    {
        Event::listen(RegisterServiceModelsEvent::class, function () {
            return [
                ServiceProvidesTest::class
            ];
        });
        $this->artisan('migrate');
        $this->expectException(TypeError::class);
        $this->artisan('service:provides');
    }

}
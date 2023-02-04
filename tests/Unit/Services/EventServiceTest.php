<?php

namespace KieranFYI\Tests\Services\Core\Unit\Services;

use Illuminate\Support\Facades\Event;
use KieranFYI\Services\Core\Events\RegisterServiceModelsEvent;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Models\ServiceModel;
use KieranFYI\Services\Core\Models\ServiceModelType;
use KieranFYI\Services\Core\Services\EventService;
use KieranFYI\Tests\Services\Core\Listeners\RegisterServiceModelsListener;
use KieranFYI\Tests\Services\Core\TestCase;

class EventServiceTest extends TestCase
{

    public function testExecute()
    {
        Event::listen(RegisterServiceModelsEvent::class, RegisterServiceModelsListener::class);
        $expected = [
            [
                Service::class,
                ServiceModel::class,
                ServiceModelType::class,
            ]
        ];
        $event = new RegisterServiceModelsEvent();
        $service = EventService::create($event);
        $response = $service->execute();
        $this->assertEquals($expected, $response);
    }

}
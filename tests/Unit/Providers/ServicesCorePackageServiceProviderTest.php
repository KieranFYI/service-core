<?php

namespace KieranFYI\Tests\Services\Core\Unit\Providers;

use Illuminate\Support\Facades\Event;
use KieranFYI\Services\Core\Events\RegisterServiceModelsEvent;

class ServicesCorePackageServiceProviderTest extends \KieranFYI\Tests\Services\Core\TestCase
{

    public function testEvent()
    {
        $expected = [
            [
                'KieranFYI\Services\Core\Models\Service',
                'KieranFYI\Services\Core\Models\ServiceModel',
                'KieranFYI\Services\Core\Models\ServiceModelType'
            ]
        ];
        $results = Event::dispatch(RegisterServiceModelsEvent::class);
        $this->assertEquals($expected, $results);
    }

}
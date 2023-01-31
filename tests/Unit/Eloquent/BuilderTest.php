<?php

namespace KieranFYI\Tests\Services\Core\Unit\Eloquent;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use KieranFYI\Services\Core\Eloquent\Builder;
use KieranFYI\Services\Core\Http\Middleware\Authenticate;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Models\ServiceModelType;
use KieranFYI\Tests\Services\Core\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BuilderTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function fakeResponse()
    {
        Http::fake([
            route('service') => function (Request $request) {
                $httpRequest = \Illuminate\Http\Request::createFromGlobals();
                $headers = $request->headers();
                $httpRequest->headers->add($headers);
                $httpRequest->json()->add($request->data());
                /** @var Authenticate $middleware */
                $middleware = $this->app->make(Authenticate::class);
                try {
                    $response = Http::response($middleware->handle($httpRequest)->content());
                    $this->assertTrue(true);
                    return $response;
                } catch (HttpException $e) {
                    return Http::response($e->getTrace(), $e->getStatusCode());
                }
            }
        ]);
    }

    public function testGet()
    {
        $this->markTestSkipped('Test causes infinite loop due to limitations with testing');

        $this->fakeResponse();

        Config::set('service.enabled', true);
        $this->fakeResponse();
        $this->artisan('migrate')->run();

        $symmetricKey = random_bytes(32);
        /** @var Service $service */
        $service = Service::create([
            'endpoint' => route('service'),
            'name' => 'Test',
            'symmetric_key' => $symmetricKey,
        ]);
        $type = ServiceModelType::create([
            'name' => Service::class
        ]);
        $service->types()->save($type, ['accessible' => false]);
        $service->load('types');

        Builder::servicesCollection()->add($service);

        $response = Service::first();
        $this->assertNotNull($response);
        $this->assertEquals(2, $this->getCount());
    }

}
<?php

namespace KieranFYI\Tests\Services\Core\Unit\Traits;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\RejectedPromise;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use KieranFYI\Services\Core\Eloquent\Builder;
use KieranFYI\Services\Core\Http\Middleware\Authenticate;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Models\ServiceModelType;
use KieranFYI\Services\Core\Services\EchoService;
use KieranFYI\Services\Core\Services\QueryService;
use KieranFYI\Services\Core\Traits\ServiceHTTPRequest;
use KieranFYI\Tests\Services\Core\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceHTTPRequestTest extends TestCase
{
    use ServiceHTTPRequest;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('app.debug', false);
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
                    return Http::response($middleware->handle($httpRequest)->content());
                } catch (HttpException $e) {
                    return Http::response($e->getTrace(), $e->getStatusCode());
                }
            }
        ]);
    }

    private function fakeResponseContentLength()
    {
        Http::fake([
            route('service') => function (Request $request) {
                return Http::response(base64_encode('InvalidLength'));
            }
        ]);
    }

    private function fakeResponseEncryption()
    {
        Http::fake([
            route('service') => function (Request $request) {
                $symmetricKey = random_bytes(16);
                $iv = random_bytes(16);
                $data = $iv . openssl_encrypt(serialize('Test'), config('app.cipher'), $symmetricKey, iv: $iv);
                return Http::response(base64_encode($data));
            }
        ]);
    }

    private function fakeResponseDelay()
    {
        Http::fake([
            route('service') => function (Request $request) {
                return new RejectedPromise(new ConnectException('Connection error', new \GuzzleHttp\Psr7\Request('get', $request->url())));
            }
        ]);
    }

    private function fakeResponseDisableRemoteCache()
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
                    Config::set('service.enabled', false);
                    $response = Http::response($middleware->handle($httpRequest)->content());
                    Config::set('service.enabled', true);
                    return $response;
                } catch (HttpException $e) {
                    Config::set('service.enabled', true);
                    return Http::response($e->getTrace(), $e->getStatusCode());
                }
            }
        ]);
    }

    public function testServicePost()
    {
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

        $testMessage = 'Testing';
        $interface = EchoService::create($testMessage);
        $response = $this->servicePost($service, $interface);
        $this->assertEquals($testMessage, $response);
    }

    public function testServicePostUnauthenticated()
    {
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
        $service->key = 'hello';

        $testMessage = 'Testing';
        $interface = EchoService::create($testMessage);
        $this->expectException(HttpException::class);
        $this->servicePost($service, $interface);
    }

    public function testServicePostForbidden()
    {
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
        ServiceModelType::create([
            'name' => Service::class
        ]);

        $builder = app()->make(Builder::class);
        $query = QueryService::create($builder, ['*'], Service::class);
        $this->expectException(HttpException::class);
        $this->servicePost($service, $query);
    }

    public function testServicePostResponseContentLength()
    {
        Config::set('service.enabled', true);
        $this->fakeResponseContentLength();
        $this->artisan('migrate')->run();

        $symmetricKey = random_bytes(32);
        /** @var Service $service */
        $service = Service::create([
            'endpoint' => route('service'),
            'name' => 'Test',
            'symmetric_key' => $symmetricKey,
        ]);

        $this->expectException(HttpException::class);
        $this->servicePost($service, EchoService::create('Test'));
    }

    public function testServicePostBadEncryption()
    {
        Config::set('service.enabled', true);
        $this->fakeResponseEncryption();
        $this->artisan('migrate')->run();

        $symmetricKey = random_bytes(32);
        /** @var Service $service */
        $service = Service::create([
            'endpoint' => route('service'),
            'name' => 'Test',
            'symmetric_key' => $symmetricKey,
        ]);
        ServiceModelType::create([
            'name' => Service::class
        ]);

        $this->expectException(HttpException::class);
        $this->servicePost($service, EchoService::create('Test'));
    }

    public function testServicePostDelay()
    {
        Config::set('service.enabled', true);
        $this->fakeResponseDelay();
        $this->artisan('migrate')->run();

        $symmetricKey = random_bytes(32);
        /** @var Service $service */
        $service = Service::create([
            'endpoint' => route('service'),
            'name' => 'Test',
            'symmetric_key' => $symmetricKey,
        ]);
        ServiceModelType::create([
            'name' => Service::class
        ]);
        $this->timeout(1);

        $this->expectException(HttpException::class);
        $this->servicePost($service, EchoService::create('Test'));
    }

    public function testServicePostDelayDebug()
    {
        Config::set('service.enabled', true);
        Config::set('app.debug', true);
        $this->fakeResponseDelay();
        $this->artisan('migrate')->run();

        $symmetricKey = random_bytes(32);
        /** @var Service $service */
        $service = Service::create([
            'endpoint' => route('service'),
            'name' => 'Test',
            'symmetric_key' => $symmetricKey,
        ]);
        ServiceModelType::create([
            'name' => Service::class
        ]);
        $this->timeout(1);

        $this->expectException(ConnectionException::class);
        $this->servicePost($service, EchoService::create('Test'));
    }
}
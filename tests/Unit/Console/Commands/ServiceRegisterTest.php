<?php

namespace Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use KieranFYI\Services\Core\Http\Middleware\Authenticate;
use KieranFYI\Tests\Services\Core\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceRegisterTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('service.encrypt', true);
    }

    private function fakeResponse()
    {
        Http::fake([
            route('service') => function (Request $request) {
                return Http::response(base64_encode(serialize(true)));
            }
        ]);
    }

    public function testHandle()
    {
        Config::set('service.encrypt', false);
        Config::set('service.enabled', true);
        $this->fakeResponse();
        $this->artisan('migrate');
        $name = 'test';
        $this->withoutMockingConsoleOutput()
            ->artisan('service:generate ' . $name);
        $token = Artisan::output();

        $token = json_decode(base64_decode($token), true);
        $token['endpoint'] = route('service');
        $token = base64_encode(json_encode($token));

        $status = $this->artisan('service:register ' . $token);
        $this->assertEquals(Command::SUCCESS, $status);
    }

    public function testHandleException()
    {
        Config::set('service.enabled', false);
        $this->artisan('service:register test')
            ->assertFailed();
    }
}
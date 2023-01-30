<?php

namespace Console\Commands;

use Illuminate\Support\Facades\Artisan;
use KieranFYI\Tests\Services\Core\TestCase;

class ServiceGenerateTest extends TestCase
{

    public function testHandle()
    {
        $this->artisan('migrate');
        $name = 'test';
        $this->withoutMockingConsoleOutput()
            ->artisan('service:generate ' . $name);
        $result = Artisan::output();
        $decoded = base64_decode($result);
        $this->assertNotFalse($decoded);

        $data = json_decode($decoded, true);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('endpoint', $data);
        $this->assertArrayHasKey('identifier', $data);
        $this->assertArrayHasKey('public_key', $data);
    }
}
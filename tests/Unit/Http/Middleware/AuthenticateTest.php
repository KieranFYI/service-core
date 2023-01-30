<?php

namespace KieranFYI\Tests\Services\Core\Unit\Http\Middleware;

use Exception;
use Illuminate\Support\Facades\Config;
use KieranFYI\Services\Core\Http\Middleware\Authenticate;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Services\EchoService;
use KieranFYI\Services\Core\Services\MaintenanceService;
use KieranFYI\Tests\Services\Core\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthenticateTest extends TestCase
{

    /**
     * @throws Exception
     */
    public function testDecryptContent()
    {
        $this->artisan('migrate')->run();

        $testMessage = 'Test';
        $symmetricKey = random_bytes(16);
        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
            'symmetric_key' => $symmetricKey,
        ]);

        $echoService = EchoService::create($testMessage);
        $echoService = serialize($echoService);
        $iv = random_bytes(16);
        $echoService = $iv . openssl_encrypt($echoService, config('app.cipher'), $symmetricKey, iv: $iv);

        request()->json()->add([
            'service' => EchoService::class,
            'content' => base64_encode($echoService),
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);
        $content = $middleware->decryptContent($service, request());

        $this->assertInstanceOf(EchoService::class, $content);
        $this->assertEquals($testMessage, $content->execute());
    }

    /**
     * @throws Exception
     */
    public function testDecryptNoSymmetricKey()
    {
        $this->artisan('migrate')->run();

        $res = openssl_pkey_new([
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        $error = openssl_error_string();
        openssl_pkey_export($res, $privateKey);
        $publicKey = openssl_pkey_get_details($res);
        $publicKey = $publicKey["key"];

        $testMessage = 'Test';
        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
            'asymmetric_key' => $privateKey
        ]);

        $echoService = EchoService::create($testMessage);
        $echoService = serialize($echoService);
        openssl_public_encrypt($echoService, $encrypted, $publicKey);

        request()->json()->add([
            'service' => EchoService::class,
            'content' => base64_encode($encrypted),
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);
        $content = $middleware->decryptContent($service, request());

        $this->assertInstanceOf(EchoService::class, $content);
        $this->assertEquals($testMessage, $content->execute());
    }

    public function testDecryptDisabled()
    {
        Config::set('service.encrypt', false);
        $this->artisan('migrate')->run();

        $testMessage = 'Test';
        $symmetricKey = random_bytes(16);
        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
            'symmetric_key' => $symmetricKey,
        ]);

        $echoService = EchoService::create($testMessage);
        $echoService = serialize($echoService);

        request()->json()->add([
            'service' => EchoService::class,
            'content' => base64_encode($echoService),
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);
        $content = $middleware->decryptContent($service, request());

        $this->assertInstanceOf(EchoService::class, $content);
        $this->assertEquals($testMessage, $content->execute());
    }

    public function testDecryptContentInvalidClass()
    {
        $this->artisan('migrate')->run();

        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);

        $this->expectException(HttpException::class);
        $middleware->decryptContent($service, request());
    }

    public function testDecryptContentInvalidContent()
    {
        $this->artisan('migrate')->run();

        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
        ]);

        request()->json()->add([
            'service' => EchoService::class,
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);

        $this->expectException(HttpException::class);
        $middleware->decryptContent($service, request());
    }

    public function testDecryptContentNullContent()
    {
        $this->artisan('migrate')->run();

        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
        ]);

        request()->json()->add([
            'service' => EchoService::class,
            'content' => null,
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);

        $this->expectException(HttpException::class);
        $middleware->decryptContent($service, request());
    }

    public function testDecryptContentEmptyContent()
    {
        $this->artisan('migrate')->run();

        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
        ]);

        request()->json()->add([
            'service' => EchoService::class,
            'content' => '',
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);

        $this->expectException(HttpException::class);
        $middleware->decryptContent($service, request());
    }


    public function testDecryptContentNonBase64()
    {
        $this->artisan('migrate')->run();

        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
        ]);

        request()->json()->add([
            'service' => EchoService::class,
            'content' => 'Test',
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);

        $this->expectException(HttpException::class);
        $middleware->decryptContent($service, request());
    }


    public function testDecryptContentNonEncrypted()
    {
        $this->artisan('migrate')->run();

        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
        ]);

        request()->json()->add([
            'service' => EchoService::class,
            'content' => base64_encode('Test'),
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);

        $this->expectException(HttpException::class);
        $middleware->decryptContent($service, request());
    }

    public function testDecryptContentBadEncryption()
    {
        $this->artisan('migrate')->run();

        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
            'symmetric_key' => random_bytes(32),
        ]);

        $symmetricKey = random_bytes(16);
        $iv = random_bytes(16);
        $content = $iv . openssl_encrypt('Test', config('app.cipher'), $symmetricKey, iv: $iv);

        request()->json()->add([
            'service' => EchoService::class,
            'content' => base64_encode($content),
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);

        $this->expectException(HttpException::class);
        $middleware->decryptContent($service, request());
    }

    public function testDecryptContentBadContent()
    {
        $this->artisan('migrate')->run();

        $symmetricKey = random_bytes(32);
        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
            'symmetric_key' => $symmetricKey
        ]);

        $iv = random_bytes(16);
        $content = $iv . openssl_encrypt('Test', config('app.cipher'), $symmetricKey, iv: $iv);

        request()->json()->add([
            'service' => EchoService::class,
            'content' => base64_encode($content),
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);

        $this->expectException(HttpException::class);
        $middleware->decryptContent($service, request());
    }

    public function testDecryptContentBadContentClass()
    {
        $this->artisan('migrate')->run();

        $symmetricKey = random_bytes(32);
        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
            'symmetric_key' => $symmetricKey
        ]);

        $iv = random_bytes(16);
        $content = $iv . openssl_encrypt(serialize(MaintenanceService::create(true)), config('app.cipher'), $symmetricKey, iv: $iv);

        request()->json()->add([
            'service' => EchoService::class,
            'content' => base64_encode($content),
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);

        $this->expectException(HttpException::class);
        $middleware->decryptContent($service, request());
    }

    public function testEncryptContent()
    {
        $this->artisan('migrate')->run();

        $symmetricKey = random_bytes(32);
        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
            'symmetric_key' => $symmetricKey
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);
        $testContent = 'Test';
        $encoded = $middleware->encryptContent($service, $testContent);

        $decoded = base64_decode($encoded);
        $iv = substr($decoded, 0, 16);
        $decrypted = openssl_decrypt(substr($decoded, 16), config('app.cipher'), $symmetricKey, iv: $iv);
        $unserialized = unserialize($decrypted);

        $this->assertEquals($testContent, $unserialized);
    }

    public function testEncryptContentDisabled()
    {
        Config::set('service.encrypt', false);
        $this->artisan('migrate')->run();

        $symmetricKey = random_bytes(32);
        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
            'symmetric_key' => $symmetricKey
        ]);

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);
        $testContent = 'Test';
        $encoded = $middleware->encryptContent($service, $testContent);

        $decoded = base64_decode($encoded);
        $unserialized = unserialize($decoded);

        $this->assertEquals($testContent, $unserialized);
    }

    /**
     * @throws Exception
     */
    public function testHandle()
    {
        Config::set('service.enabled', true);
        $this->artisan('migrate')->run();

        $testMessage = 'Test';
        $symmetricKey = random_bytes(32);
        /** @var Service $service */
        $service = Service::create([
            'name' => 'Test',
            'symmetric_key' => $symmetricKey,
        ]);
        $this->be($service);

        $echoService = EchoService::create($testMessage);
        $echoService = serialize($echoService);
        $iv = random_bytes(16);
        $echoService = $iv . openssl_encrypt($echoService, config('app.cipher'), $symmetricKey, iv: $iv);

        request()->headers->add([
            'Authorization' => 'Bearer ' . $service->key
        ]);
        request()->json()->add([
            'service' => EchoService::class,
            'content' => base64_encode($echoService),
        ]);
        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);
        $response = $middleware->handle(request());

        $content = $response->content();
        $content = base64_decode($content);
        $key = substr($content, 0, 16);
        $response = openssl_decrypt(substr($content, 16), config('app.cipher'), $symmetricKey, iv: $key);
        $response = unserialize($response);

        $this->assertEquals($testMessage, $response);
    }

    /**
     * @throws Exception
     */
    public function testHandleDisabled()
    {
        Config::set('service.enabled', false);
        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);
        $this->expectException(HttpException::class);
        $middleware->handle(request());
    }

    /**
     * @throws Exception
     */
    public function testHandleNoToken()
    {
        Config::set('service.enabled', true);
        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);
        $this->expectException(HttpException::class);
        $middleware->handle(request());
    }
}
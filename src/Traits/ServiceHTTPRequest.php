<?php

namespace KieranFYI\Services\Core\Traits;

use Exception;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use KieranFYI\Misc\Facades\Debugbar;
use KieranFYI\Misc\Traits\HTTPClientTrait;
use KieranFYI\Services\Core\Interfaces\ServiceInterface;
use KieranFYI\Services\Core\Models\Service;

trait ServiceHTTPRequest
{
    use HTTPClientTrait;

    /**
     * @param Service $service
     * @param ServiceInterface $interface
     * @return Response
     */
    protected function servicePost(Service $service, ServiceInterface $interface): mixed
    {
        Debugbar::debug('Sending ' . $interface::class);
        return Debugbar::measure('ServiceBuilder', function () use ($service, $interface) {
            try {
                $data = serialize($interface);
                if (config('service.encrypt')) {
                    $iv = random_bytes(16);
                    $data = $iv . openssl_encrypt($data, config('app.cipher'), $service->symmetric_key, iv: $iv);
                }

                $response = $this->auth('Bearer ' . $service->key)
                    ->userAgent(config('app.name'))
                    ->client()
                    ->post($service->endpoint, [
                        'service' => get_class($interface),
                        'content' => base64_encode($data),
                    ]);

                if ($response->unauthorized()) {
                    abort(511);
                } else if ($response->forbidden()) {
                    abort(510);
                }
                $response->throw();

                $content = base64_decode($response->body());
                if (config('service.encrypt')) {
                    if (strlen($content) < 16) {
                        abort(502);
                    }

                    $key = substr($content, 0, 16);
                    $encrypted = substr($content, 16);
                    $content = openssl_decrypt($encrypted, config('app.cipher'), $service->symmetric_key, iv: $key);
                    if ($encrypted === $content || $content === false) {
                        abort(502);
                    }
                }

                return unserialize($content);
            } catch (ConnectionException $e) {
                throw_if(config('app.debug'), $e);
                abort(504);
            } catch (Exception $e) {
                throw_if(config('app.debug'), $e);
                abort(502);
            }
        });
    }
}
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
     * @param Encrypter $decrypter
     * @param string $endpoint
     * @param array $data
     * @return Response
     */
    protected function post(Service $service, ServiceInterface $interface): mixed
    {
        Debugbar::debug('Sending ' . $interface::class);
        return Debugbar::measure('ServiceBuilder', function () use ($service, $interface) {
            try {
                $data = serialize($interface);
                if (config('service.encrypt')) {
                    $data = $service->encrypter->encrypt($data);
                }

                $startTime = microtime(true);
                $response = $this->auth('Bearer ' . $service->key)
                    ->userAgent(config('app.name'))
                    ->client()
                    ->post($service->endpoint, [
                        'content' => base64_encode($data),
                        'service' => get_class($interface),
                    ]);
                $time = microtime(true) - $startTime;

                if (config('app.debug') && $response->failed()) {
                    $body = $response->body();
                    die($body);
                    if (config('service.encrypt')) {
                        $body = Crypt::decrypt($body);
                    }
                }
                if ($response->unauthorized()) {
                    abort(511);
                } else if ($response->forbidden()) {
                    abort(510);
                }

                $threshold = config('debugbar.options.db.slow_threshold', false);
                if (!$threshold || $time > $threshold) {
                    if (Debugbar::hasCollector('queries')) {
                        Debugbar::getCollector('queries')->addQuery($this->toSql(), $this->bindings, $time, DB::connection());
                    }
                }
                $body = $response->body();
                if (config('service.encrypt')) {
                    $body = Crypt::decrypt($body);
                }

                return base64_decode(unserialize($body));
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
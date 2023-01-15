<?php

namespace KieranFYI\Services\Core\Eloquent;

use Exception;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use KieranFYI\Misc\Facades\Debugbar;
use KieranFYI\Misc\Traits\HTTPRequestTrait;
use Laravie\SerializesQuery\Query;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Builder extends QueryBuilder
{
    use HTTPRequestTrait;

    /**
     * @var string|null
     */
    private ?string $serviceModel = null;

    /**
     * @param array|string|string[] $columns
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*'])
    {
        $endpoint = null;
        foreach (config('service.endpoints', []) as $key => $models) {
            if (!in_array($this->serviceModel, $models)) {
                continue;
            }

            $endpoint = $key;
            break;
        }
        if (is_null($endpoint)) {
            Debugbar::debug('No service endpoint found');
            return parent::get($columns);
        }

        return Debugbar::measure('ServiceBuilder', function () use ($endpoint, $columns) {
            try {
                $startTime = microtime(true);
                $response = $this->timeout(1)
                    ->auth('Bearer ' . config('service.token'))
                    ->userAgent(config('app.name'))
                    ->post($endpoint, [
                        'query' => Query::serialize($this),
                        'columns' => $columns,
                        'model' => $this->serviceModel,
                    ]);

                $time = microtime(true) - $startTime;
                if ($response->unauthorized()) {
                    abort(511);
                } else if ($response->forbidden()) {
                    abort(510);
                }

                if ($response->failed()) {
                    die($response->body());
                }

                $threshold = config('debugbar.options.db.slow_threshold', false);
                if (!$threshold || $time > $threshold) {
                    if (Debugbar::hasCollector('queries')) {
                        Debugbar::getCollector('queries')->addQuery($this->toSql(), $this->bindings, $time, DB::connection());
                    }
                }

                return $response->collect();
            } catch (HttpException $e) {
                throw $e;
            } catch (ConnectionException $e) {
                abort(504);
            } catch (Exception $e) {
                abort(502);
            }
        });
    }

    /**
     * @param string $model
     * @return $this
     */
    public function serviceModel(string $model): static
    {
        $this->serviceModel = $model;
        return $this;
    }
}
<?php

namespace KieranFYI\Services\Core\Eloquent;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use KieranFYI\Misc\Facades\Debugbar;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Services\QueryService;
use KieranFYI\Services\Core\Traits\ServiceHTTPRequest;

class Builder extends QueryBuilder
{
    use ServiceHTTPRequest;

    /**
     * @var Collection|null
     */
    private static Collection|null $servicesCollection = null;

    /**
     * @var string|null
     */
    private ?string $serviceModel = null;

    public static function servicesCollection(): ?Collection
    {
        if (is_null(self::$servicesCollection)) {
            self::$servicesCollection = collect();
            self::$servicesCollection = Service::get();
        }
        return self::$servicesCollection;
    }

    /**
     * @param array|string|string[] $columns
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*'])
    {
        $service = self::servicesCollection()->first(function (Service $service) {
            return $service->relationLoaded('types') && $service->types->contains('name', $this->serviceModel);
        });

        if (is_null($service)) {
            Debugbar::debug('No service found');
            return parent::get($columns);
        }

        /*
         * This can't be tested because any lookup that exists
         * in our test database will cause an infinite lookup
         * as each endpoint tries to resolve it causing a memory issue.
         */
        // @codeCoverageIgnoreStart
        return $this->servicePost($service, QueryService::create($this, $columns, $this->serviceModel));
        // @codeCoverageIgnoreEnd
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
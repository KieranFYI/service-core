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

    /**
     * @param array|string|string[] $columns
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*'])
    {
        if (is_null(static::$servicesCollection)) {
            // Set this to a non null value to "imply" the models have loaded and hit the database directly
            // This should only run on the first database call of each website lifecycle.
            static::$servicesCollection = collect();
            static::$servicesCollection = Service::get();
        }

        $service = static::$servicesCollection->first(function (Service $service) {
            return $service->types->contains($this->serviceModel);
        });

        if (is_null($service)) {
            Debugbar::debug('No service found');
            return parent::get($columns);
        }

        $query = new QueryService($this, $columns, $this->serviceModel);
        $this->post($service, $query);

        return $query->collect();
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
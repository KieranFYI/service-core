<?php

namespace KieranFYI\Services\Core\Eloquent;

use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Grammar;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Processors\Processor;
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
     * @var Collection|null
     */
    private static Collection|null $serviceTablesCollection = null;

    /**
     * @var string|null
     */
    private ?string $serviceModel = null;

    /**
     * @return Collection|null
     */
    public static function servicesCollection(): ?Collection
    {
        if (is_null(self::$servicesCollection)) {
            self::$servicesCollection = collect();
            self::$servicesCollection = Service::get();
        }
        return self::$servicesCollection;
    }

    /**
     * @return void
     */
    public static function clearServicesCollection(): void
    {
        self::$servicesCollection = collect();
    }

    /**
     * @param string|null $item
     * @return Service|null
     */
    public static function service(?string $item): ?Service
    {
        $results = self::servicesCollection()
            ->filter();

        if ($results->isEmpty()) {
            return null;
        }

        return $results
            ->where(function (Service $service) use ($item) {
                return $service->relationLoaded('types') && $service->types->contains('name', $item);
            })
            ->first();
    }

    public static function serviceTablesCollection(): ?Collection
    {
        $services = self::servicesCollection();

        return $services
            ->pluck('types')
            ->flatten(1)->pluck('name')
            ->mapWithKeys(function (string $name) {
                return [app($name)->getTable() => $name];
            });
    }

    /**
     * @param string $item
     * @return ?string
     */
    public static function serviceFromTable(string $table): ?string
    {
        return self::serviceTablesCollection()->get($table);
    }

    /**
     * Set the table which the query is targeting.
     *
     * @param Closure|QueryBuilder|\Illuminate\Database\Eloquent\Builder|string $table
     * @param string|null $as
     * @return $this
     */
    public function from($table, $as = null)
    {
        if (!$this->isQueryable($table)) {
            $this->serviceModel = $this->serviceFromTable($table);
        }

        return parent::from($table, $as);
    }

    /**
     * @param array|string|string[] $columns
     * @return Collection
     */
    public function get($columns = ['*'])
    {

        $service = self::service($this->serviceModel);
        if (!is_null($service)) {
            // @codeCoverageIgnoreStart
            Debugbar::debug('No service found');
            /*
             * This can't be tested because any lookup that exists
             * in our test database will cause an infinite lookup
             * as each endpoint tries to resolve it causing a memory issue.
             */
            return $this->servicePost($service, QueryService::create($this, $columns, $this->serviceModel));
            // @codeCoverageIgnoreEnd
        }
        return parent::get($columns);
    }
}
<?php

namespace KieranFYI\Services\Core\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use KieranFYI\Services\Core\Eloquent\Builder;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Models\ServiceModelType;
use Laravie\SerializesQuery\Eloquent;
use Laravie\SerializesQuery\Query;

class QueryService extends AbstractService
{
    /**
     * @var array
     */
    private array $builder;

    /**
     * @var array|string|string[]
     */
    private mixed $columns;

    /**
     * @var string
     */
    private string $model;

    /**
     * @var Collection|null
     */
    private ?Collection $collection = null;

    /**
     * @param Builder $builder
     * @param array|string|string[] $columns
     * @param string $model
     */
    public function __construct(Builder $builder, array|string $columns, string $model)
    {
        $this->builder = Query::serialize($builder);
        $this->columns = $columns;
        $this->model = $model;
    }

    public function execute(): ?Collection
    {
        /** @var Service $service */
        $service = Auth::user();
        if (!($service instanceof Service)) {
            return null;
        }
        $type = ServiceModelType::where('name', $this->model)
            ->firstOrfail();

        /** @var ServiceModelType $serviceType */
        $serviceType = $service->types->firstWhere('name', $type->name);
        if (is_null($serviceType)) {
            $service->types()->syncWithPivotValues($type, ['last_used_at' => Carbon::now()], false);
            abort(403);
        } else if (!$serviceType->pivot->accessible) {
            $serviceType->update(['last_used_at' => Carbon::now()]);
            abort(403);
        }
        $serviceType->update(['last_used_at' => Carbon::now()]);

        $query = Eloquent::unserialize([
            'model' => [
                'class' => $this->model,
                'connection' => config('database.default'),
                'removedScopes' => [],
                'eager' => [],
            ],
            'builder' => $this->builder
        ]);

        return $query->get($this->columns);
    }
}
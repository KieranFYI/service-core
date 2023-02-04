<?php

namespace KieranFYI\Services\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use KieranFYI\Services\Core\Events\RegisterServiceModelsEvent;
use KieranFYI\Services\Core\Models\ServiceModelType;
use KieranFYI\Services\Core\Traits\ServiceHTTPRequest;
use TypeError;

class ServiceProvides extends Command
{
    use ServiceHTTPRequest;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:provides';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Registers all the provided services into the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $results = Event::dispatch(RegisterServiceModelsEvent::class);
        $types = $this->types(array_values(Relation::$morphMap));
        foreach ($results as $models) {
            if (!is_array($models)) {
                throw new TypeError(self::class . '::handle(): $models must be of array ' . Model::class);
            }
            $types = array_merge($types, $this->types($models));
        }

        ServiceModelType::whereNotIn('name', $types)
            ->update(['enabled' => false]);

        foreach ($types as $type) {
            ServiceModelType::create(['name' => $type]);
        }

        return Command::SUCCESS;
    }

    /**
     * @param array $models
     * @return array
     */
    private function types(array $models): array
    {
        $types = [];
        foreach ($models as $model) {
            if (!is_a($model, Model::class, true)) {
                throw new TypeError(self::class . '::handle(): $model must be of type ' . Model::class);
            }

            $types[] = $model;
        }

        return $types;
    }

}

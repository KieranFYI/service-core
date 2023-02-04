<?php

namespace KieranFYI\Tests\Services\Core\Listeners;


use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Models\ServiceModel;
use KieranFYI\Services\Core\Models\ServiceModelType;

class RegisterServiceModelsListener
{
    /**
     * Handle the event.
     *
     * @return array
     */
    public function handle(): array
    {
        return [
            Service::class,
            ServiceModel::class,
            ServiceModelType::class,
        ];
    }
}
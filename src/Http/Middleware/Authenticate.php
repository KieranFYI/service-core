<?php

namespace KieranFYI\Services\Core\Http\Middleware;

use KieranFYI\Services\Core\Services\EchoService;
use KieranFYI\Services\Core\Services\EventService;
use KieranFYI\Services\Core\Services\MaintenanceService;
use KieranFYI\Services\Core\Services\QueryService;
use KieranFYI\Services\Core\Services\RegistrationService;

class Authenticate extends AbstractAuthenticate
{
    /**
     * @var string
     */
    protected string $guard = 'services';

    /**
     * @var array|string[]
     */
    protected array $allowed = [
        EchoService::class,
        EventService::class,
        MaintenanceService::class,
        QueryService::class,
        RegistrationService::class,
    ];
}

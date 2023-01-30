<?php

namespace KieranFYI\Services\Core\Services;

use Illuminate\Support\Facades\Artisan;

class MaintenanceService extends AbstractService
{

    /**
     * @var bool
     */
    private bool $up;

    public function __construct(bool $up)
    {
        $this->up = $up;
    }

    public function execute()
    {
        if ($this->up) {
            Artisan::call('up');
        } else {
            Artisan::call('down');
        }
    }
}
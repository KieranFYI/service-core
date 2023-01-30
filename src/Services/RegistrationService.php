<?php

namespace KieranFYI\Services\Core\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use KieranFYI\Services\Core\Models\Service;

class RegistrationService extends AbstractService
{

    /**
     * @var string
     */
    private string $symmetricKey;

    /**
     * @var string
     */
    private string $endpoint;

    public function __construct(string $symmetricKey)
    {
        $this->symmetricKey = $symmetricKey;
        $this->endpoint = route('service');
    }

    /**
     * @return Collection
     */
    public function execute(): Collection
    {
        /** @var Service $service */
        $service = Auth::user();
        abort_if(isset($service->symmetric_key), 418);

        $service->update([
            'symmetric_key' => $this->symmetricKey,
            'endpoint' => $this->endpoint,
        ]);

        return $service->types->pluck('name');
    }
}
<?php

namespace KieranFYI\Services\Core\Services;

use Illuminate\Support\Facades\Event;

class EventService extends AbstractService
{

    /**
     * @var mixed
     */
    private mixed $event;

    public function __construct(mixed $event)
    {
        $this->event = $event;
    }

    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        return Event::dispatch($this->event);
    }
}
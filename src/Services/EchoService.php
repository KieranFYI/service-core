<?php

namespace KieranFYI\Services\Core\Services;

class EchoService extends AbstractService
{

    /**
     * @var string
     */
    private string $message;

    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function execute(): string
    {
        return $this->message;
    }
}
<?php

namespace KieranFYI\Services\Core\Services;

use KieranFYI\Services\Core\Interfaces\ServiceInterface;

abstract class AbstractService implements ServiceInterface
{

    /**
     * @param array $args
     * @return static
     */
    public static function create(...$args): static
    {
        return new static(...$args);
    }
}
<?php

namespace KieranFYI\Services\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use KieranFYI\Services\Core\Eloquent\Builder;

/**
 * @mixin Model
 */
trait Serviceable
{

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();
        return new Builder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
    }
}
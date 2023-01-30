<?php

namespace KieranFYI\Tests\Services\Core\Unit\Traits;

use KieranFYI\Services\Core\Eloquent\Builder;
use KieranFYI\Tests\Services\Core\Models\ServiceableModel;
use KieranFYI\Tests\Services\Core\TestCase;

class ServiceableTraitTest extends TestCase
{

    public function testNewBaseQueryBuilder()
    {
        $model = new ServiceableModel();
        $this->assertInstanceOf(Builder::class, $model->newModelQuery()->getQuery());
    }
}
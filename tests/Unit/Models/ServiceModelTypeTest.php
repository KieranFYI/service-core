<?php

namespace KieranFYI\Tests\Services\Core\Unit\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use KieranFYI\Logging\Traits\LoggingTrait;
use KieranFYI\Misc\Traits\KeyedTitle;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Models\ServiceModelType;
use KieranFYI\Services\Core\Traits\Serviceable;
use KieranFYI\Tests\Services\Core\TestCase;

class ServiceModelTypeTest extends TestCase
{

    /**
     * @var ServiceModelType
     */
    private ServiceModelType $model;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ServiceModelType();
    }

    public function testModel()
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function testTraits()
    {
        $uses = class_uses_recursive(Service::class);
        $this->assertContains(Serviceable::class, $uses);
        $this->assertContains(LoggingTrait::class, $uses);
        $this->assertContains(KeyedTitle::class, $uses);
    }

    public function testFillable()
    {
        $fillable = [
            'name'
        ];
        $this->assertEquals($fillable, $this->model->getFillable());
    }

    public function testCasts()
    {
        $casts = [
            'last_used_at' => 'datetime',
            'accessible' => 'boolean',
            'id' => 'int',
        ];

        $this->assertEquals($casts, $this->model->getCasts());
    }

    public function testVisible()
    {
        $visible = [
            'service_model_type_id',
            'service_id'
        ];

        $this->assertEquals($visible, $this->model->getVisible());
    }

    public function testTitle()
    {
        $this->model->name = 'Test';
        $this->assertEquals('Test', $this->model->getTitleAttribute());
    }

    public function testServices()
    {
        $this->assertInstanceOf(BelongsToMany::class, $this->model->services());
        $this->assertInstanceOf(Collection::class, $this->model->services);
    }
}
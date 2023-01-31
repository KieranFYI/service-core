<?php

namespace KieranFYI\Tests\Services\Core\Unit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Models\ServiceModel;
use KieranFYI\Services\Core\Models\ServiceModelType;
use KieranFYI\Services\Core\Traits\Serviceable;
use KieranFYI\Tests\Services\Core\TestCase;

class ServiceModelTest extends TestCase
{

    /**
     * @var ServiceModel
     */
    private ServiceModel $model;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ServiceModel();
    }

    public function testModel()
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function testTraits()
    {
        $uses = class_uses_recursive(Service::class);
        $this->assertContains(Serviceable::class, $uses);
    }

    public function testCasts()
    {
        $casts = [
            'last_used_at' => 'datetime'
        ];

        $this->assertEquals($casts, $this->model->getCasts());
    }

    public function testHidden()
    {
        $hidden = [
            'service_model_type_id',
            'service_id'
        ];

        $this->assertEquals($hidden, $this->model->getHidden());
    }

    public function testService()
    {
        $this->artisan('migrate')->run();

        $this->assertInstanceOf(BelongsTo::class, $this->model->service());

        $service = Service::create(['name' => 'Test']);
        $this->model->service()->associate($service);

        $this->assertTrue($service->is($this->model->service));
    }

    public function testModelRelationship()
    {
        $this->artisan('migrate')->run();

        $this->assertInstanceOf(BelongsTo::class, $this->model->model());

        $type = ServiceModelType::create(['name' => 'Test']);
        $this->model->service()->associate($type);

        $this->assertTrue($type->is($this->model->service));
    }
}
<?php

namespace KieranFYI\Tests\Services\Core\Unit\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use KieranFYI\Logging\Traits\LoggingTrait;
use KieranFYI\Misc\Traits\KeyedTitle;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Traits\Serviceable;
use KieranFYI\Tests\Services\Core\TestCase;

class ServiceTest extends TestCase
{

    /**
     * @var Service
     */
    private Service $model;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Service();
    }

    public function testModel()
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function testTraits()
    {
        $uses = class_uses_recursive(Service::class);
        $this->assertContains(SoftDeletes::class, $uses);
        $this->assertContains(LoggingTrait::class, $uses);
        $this->assertContains(Serviceable::class, $uses);
        $this->assertContains(KeyedTitle::class, $uses);
    }

    public function testFillable()
    {
        $fillable = [
            'name', 'last_used_at', 'asymmetric_key', 'symmetric_key', 'endpoint', 'key'
        ];
        $this->assertEquals($fillable, $this->model->getFillable());
    }

    public function testCasts()
    {
        $casts = [
            'last_used_at' => 'datetime',
            'accessible' => 'boolean',
            'asymmetric_key' => 'encrypted',
            'id' => 'int',
            'deleted_at' => 'datetime',
        ];

        $this->assertEquals($casts, $this->model->getCasts());
    }

    public function testHidden()
    {
        $hidden = [
            'asymmetric_key', 'symmetric_key'
        ];

        $this->assertEquals($hidden, $this->model->getHidden());
    }

    public function testTitle()
    {
        $this->model->name = 'Test';
        $this->assertEquals('Test', $this->model->getTitleAttribute());
    }

    public function testTypes()
    {
        $this->assertInstanceOf(BelongsToMany::class, $this->model->types());
        $this->assertInstanceOf(Collection::class, $this->model->types);
    }
}
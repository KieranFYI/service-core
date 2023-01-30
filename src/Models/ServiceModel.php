<?php

namespace KieranFYI\Services\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use KieranFYI\Services\Core\Traits\Serviceable;

/**
 * @property string $name
 * @property Carbon $last_used_at
 * @property Service $service
 * @property ServiceModelType $model
 */
class ServiceModel extends Pivot
{
    use Serviceable;

    /**
     * @var string[]
     */
    protected $hidden = [
        'service_model_type_id',
        'service_id'
    ];

    protected $casts = [
        'last_used_at' => 'datetime'
    ];

    /**
     * @return BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsTo
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(ServiceModelType::class);
    }
}

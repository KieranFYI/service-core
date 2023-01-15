<?php

namespace KieranFYI\Services\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use KieranFYI\Logging\Traits\LoggingTrait;

/**
 * @property string $name
 * @property Carbon $last_used_at
 * @property Collection $services
 */
class ServiceModelType extends Model
{
    use LoggingTrait;

    /**
     * @var string[]
     */
    protected $casts = [
        'last_used_at' => 'datetime',
        'accessible' => 'boolean'
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'name'
    ];

    /**
     * @var string[]
     */
    protected $visible = [
        'service_model_type_id',
        'service_id'
    ];

    /**
     * @return BelongsToMany
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, ServiceModel::class)
            ->withPivot('last_used_at', 'accessible');
    }
}

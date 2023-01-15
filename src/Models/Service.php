<?php

namespace KieranFYI\Services\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use KieranFYI\Logging\Traits\LoggingTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use KieranFYI\Services\Core\Traits\Serviceable;

/**
 * @property string $name
 * @property Carbon $last_used_at
 * @property bool $accessible
 * @property Collection $types
 */
class Service extends Authenticatable
{
    use SoftDeletes;
    use LoggingTrait;
    use Serviceable;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name', 'last_used_at', 'key'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'last_used_at' => 'datetime',
        'accessible' => 'boolean',
    ];

    /**
     * @var string[]
     */
    protected $with = [
        'types'
    ];

    /**
     * @return void
     */
    protected static function booting()
    {
        static::creating(function ($model) {
            $model->key = (string)Str::uuid();
        });
    }

    /**
     * @return BelongsToMany
     */
    public function types(): BelongsToMany
    {
        return $this->belongsToMany(ServiceModelType::class, ServiceModel::class)
            ->withTimestamps()
            ->withPivot('last_used_at', 'accessible');
    }

}
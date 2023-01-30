<?php

namespace KieranFYI\Services\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use KieranFYI\Logging\Traits\LoggingTrait;
use KieranFYI\Misc\Traits\HasKeyTrait;
use KieranFYI\Misc\Traits\KeyedTitle;
use KieranFYI\Services\Core\Traits\Serviceable;

/**
 * @property string $name
 * @property Carbon $last_used_at
 * @property bool $accessible
 * @property Collection $types
 * @property string $asymmetric_key
 * @property string $symmetric_key
 * @property Encrypter $encrypter
 * @property string $endpoint
 * @property string $key
 */
class Service extends Authenticatable
{
    use SoftDeletes;
    use LoggingTrait;
    use Serviceable;
    use KeyedTitle;
    use HasKeyTrait;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name', 'last_used_at', 'asymmetric_key', 'symmetric_key', 'endpoint'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'last_used_at' => 'datetime',
        'accessible' => 'boolean',
        'asymmetric_key' => 'encrypted'
    ];

    /**
     * @var string[]
     */
    protected $hidden = [
        'asymmetric_key',
        'symmetric_key'
    ];

    /**
     * @var string[]
     */
    protected $with = [
        'types'
    ];

    /**
     * @var string
     */
    protected string $title_key = 'name';

    /**
     * @return BelongsToMany
     */
    public function types(): BelongsToMany
    {
        return $this->belongsToMany(ServiceModelType::class, ServiceModel::class)
            ->withTimestamps()
            ->withPivot('last_used_at', 'accessible');
    }

    /**
     * @return string|null
     */
    public function getSymmetricKeyAttribute(): ?string
    {
        if (empty($this->attributes['symmetric_key'])) {
            return null;
        }
        return base64_decode(Crypt::decryptString($this->attributes['symmetric_key']));
    }

    /**
     * @param string $value
     * @return void
     */
    public function setSymmetricKeyAttribute(string $value): void
    {
        $this->attributes['symmetric_key'] = Crypt::encryptString(base64_encode($value));
    }
}
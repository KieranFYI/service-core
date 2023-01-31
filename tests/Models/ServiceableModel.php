<?php

namespace KieranFYI\Tests\Services\Core\Models;

use Illuminate\Database\Eloquent\Model;
use KieranFYI\Services\Core\Traits\Serviceable;

class ServiceableModel extends Model
{
    use Serviceable;
}
<?php

use Illuminate\Support\Facades\Route;
use KieranFYI\Services\Core\Http\Controllers\ServicesExecutionController;

Route::middleware(['services.auth'])->post(config('service.path'), [ServicesExecutionController::class, 'execute']);
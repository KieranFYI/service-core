<?php

use Illuminate\Support\Facades\Route;
use KieranFYI\Services\Core\Http\Middleware\Authenticate;

Route::middleware([Authenticate::class])
    ->group(function () {
        Route::any(config('service.path'), function () {
            abort(418);
        })
            ->name('service');
    });
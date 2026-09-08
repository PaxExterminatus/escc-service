<?php

use App\Domain\App\Container\Controllers\ContainerController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/')
    ->group(function () {
        Route::get('container/{id}', [ContainerController::class, 'show']);
        Route::post('container/{id}/stop', [ContainerController::class, 'stop']);
        Route::post('container/{id}/start', [ContainerController::class, 'start']);
        Route::post('container/{id}/status/{statusId}', [ContainerController::class, 'setStatus'])
            ->where('statusId', '-?[0-9]+');
    });

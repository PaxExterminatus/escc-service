<?php

use App\Domain\Cabinet\Controllers\CabinetClientController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/')
    ->group(function () {
        Route::get('client/{id}', [CabinetClientController::class, 'show']);
    });

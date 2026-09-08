<?php

use App\Domain\Cabinet\Controllers\CabinetClientController;
use App\Domain\Cabinet\Controllers\CabinetClientFinanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/')
    ->group(function () {
        Route::get('client/{id}', [CabinetClientController::class, 'show']);
        Route::get('client/{id}/finance-history', [CabinetClientFinanceController::class, 'history']);
    });

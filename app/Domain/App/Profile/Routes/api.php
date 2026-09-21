<?php

use App\Domain\App\Profile\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/')
    ->group(function () {
        /** Поиск клиентов — выше {id}, иначе он перехватил бы 'search' как несуществующий id */
        Route::get('clients/search', [ProfileController::class, 'search']);

        Route::get('profile/{id}', [ProfileController::class, 'show']);
        Route::put('profile/{id}/communication', [ProfileController::class, 'updateCommunication']);
    });

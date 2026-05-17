<?php

use App\Domain\App\Profile\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/')
    ->group(function () {
        Route::get('profile/{id}', [ProfileController::class, 'show']);
    });

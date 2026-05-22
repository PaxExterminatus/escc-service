<?php

use App\Domain\EFront\Controllers\EFrontController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/efront/')
    ->group(function () {
        Route::match(['get', 'post'],'data/', [EFrontController::class, 'data']);
    });

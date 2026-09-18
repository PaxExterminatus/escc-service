<?php

use App\Domain\App\Course\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/')
    ->group(function () {
        Route::get('clients/{clientId}/courses', [CourseController::class, 'index']);
        Route::get('courses/{subId}/containers', [CourseController::class, 'containers']);
    });

<?php

use App\Domain\App\Course\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/')
    ->group(function () {
        /** Поиск курсов — выше {subId}, иначе он перехватил бы 'search' как несуществующий id */
        Route::get('courses/search', [CourseController::class, 'search']);

        Route::get('clients/{clientId}/courses', [CourseController::class, 'index']);
        Route::get('courses/{subId}/containers', [CourseController::class, 'containers']);
        Route::get('courses/{subId}', [CourseController::class, 'show'])->whereNumber('subId');
    });

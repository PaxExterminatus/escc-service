<?php

use App\Domain\Scheduler\Controllers\ScheduledTaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/scheduler/')
    ->group(function () {
        Route::get('tasks', [ScheduledTaskController::class, 'index']);
        Route::put('tasks/{taskId}', [ScheduledTaskController::class, 'update'])->whereNumber('taskId');
    });

<?php

use App\Http\Controllers\SPAController;
use Illuminate\Support\Facades\Route;


Route::get('/', SPAController::class)->name('home');

Route::prefix('/clients/')->group(function () {
    Route::get('profile', SPAController::class);
});

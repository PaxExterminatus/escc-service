<?php

use App\Http\Controllers\SPA\SPAController;
use Illuminate\Support\Facades\Route;

Route::get('/', SPAController::class)->name('home');

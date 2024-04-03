<?php

use App\Http\Controllers\SPAController;
use Illuminate\Support\Facades\Route;


Route::get('/', SPAController::class)->name('home');

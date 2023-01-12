<?php

use App\Http\Controllers\SPA\SPAController;
use Illuminate\Support\Facades\Route;

Route::get('/', SPAController::class)->name('home');
Route::get('/messages/sending', SPAController::class)->name('messages-sending');

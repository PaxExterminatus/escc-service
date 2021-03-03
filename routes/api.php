<?php

use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\EFrontController;
use Illuminate\Support\Facades\Route;

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('/client/{id}', [ClientController::class, 'show']);

Route::get('/efront/data', [EFrontController::class, 'data']);

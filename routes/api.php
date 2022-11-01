<?php

use App\Domain\Messages\Controllers\DailyMessagingController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\EFrontController;
use Illuminate\Support\Facades\Route;

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('/client/{id}', [ClientController::class, 'show']);

Route::match(['get', 'post'], '/efront/data', [EFrontController::class, 'data']);

/**
 * @example /api/messages/daily/sms/send
 * @example /api/messages/daily/email/send
 * @example /api/messages/daily/viber/send
 * @example /api/messages/daily/telegram/send
 */
Route::get('/messages/daily/{messageType}/send', []);

/**
 * @example /api/messages/daily/sms
 * @example /api/messages/daily/email
 * @example /api/messages/daily/viber
 * @example /api/messages/daily/telegram
 */
Route::get('/messages/daily/{messageType}/', [DailyMessagingController::class, 'index']);

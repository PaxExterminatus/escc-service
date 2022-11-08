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
 * @example /api/messages/daily/sms
 * @example /api/messages/daily/email
 * @example /api/messages/daily/viber
 * @example /api/messages/daily/telegram
 */
Route::get('/messages/daily/{messageType}/', [DailyMessagingController::class, 'index']);

/**
 * @example /api/messages/daily/sms/send
 * @example /api/messages/daily/email/send
 * @example /api/messages/daily/viber/send
 * @example /api/messages/daily/telegram/send
 */
Route::get('/messages/daily/{messageType}/send', [DailyMessagingController::class, 'send']);

/**
 * @example /api/messages/daily/sms/txt
 * @example /api/messages/daily/email/txt
 * @example /api/messages/daily/viber/txt
 * @example /api/messages/daily/telegram/txt
 */
Route::get('/messages/daily/{messageType}/txt', [DailyMessagingController::class, 'txt']);

<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Messages\Controllers\DailyMessagingController;
use App\Domain\Messages\Controllers\MessageSendController;
use App\Domain\Messages\Controllers\MessageTemplateController;

Route::prefix('api/messages/')
    ->group(function () {
        /** Templates: list/create/update/delete (shared by SMS and Email) */
        Route::get('templates', [MessageTemplateController::class, 'index']);
        Route::post('templates', [MessageTemplateController::class, 'store']);
        Route::put('templates/{id}', [MessageTemplateController::class, 'update']);
        Route::delete('templates/{id}', [MessageTemplateController::class, 'destroy']);
        Route::get('templates/{id}/render', [MessageTemplateController::class, 'render']);

        /** Recipient status (address + consent) for a client, per channel */
        Route::get('recipient/{clientId}', [MessageSendController::class, 'recipient']);

        /** Send one message (SMS or Email) to a client */
        Route::post('send', [MessageSendController::class, 'send']);

        /**
         * @example /api/messages/daily/sms
         * @example /api/messages/daily/email
         */
        Route::get('daily/{type}/', [DailyMessagingController::class, 'index']);

        /**
         * @example /api/messages/daily/sms/send
         * @example /api/messages/daily/email/send
         */
        Route::get('daily/{type}/send', [DailyMessagingController::class, 'send']);

        /**
         * @example /api/messages/daily/sms/txt
         * @example /api/messages/daily/email/txt
         */
        Route::get('daily/{type}/txt', [DailyMessagingController::class, 'txt']);
    });

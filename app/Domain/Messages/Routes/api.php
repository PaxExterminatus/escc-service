<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Messages\Controllers\DailyMessagingController;
use App\Domain\Messages\Controllers\MessageHistoryController;
use App\Domain\Messages\Controllers\MessageQueueController;
use App\Domain\Messages\Controllers\MessageSendController;

Route::prefix('api/messages/')
    ->group(function () {
        /** Recipient status (address + consent) for a client, per channel */
        Route::get('recipient/{clientId}', [MessageSendController::class, 'recipient']);

        /** История уведомлений клиента (SMS/Email) — уже отправленное + то, что ещё в очереди */
        Route::get('history/{clientId}', [MessageHistoryController::class, 'forClient']);

        /** Send one message (SMS or Email) to a client */
        Route::post('send', [MessageSendController::class, 'send']);

        /** Редактор очереди: добавить/изменить/удалить одну строку дневной рассылки (пока wait) */
        Route::post('queue', [MessageQueueController::class, 'store']);
        Route::put('queue/{id}', [MessageQueueController::class, 'update'])->whereNumber('id');
        Route::delete('queue/{id}', [MessageQueueController::class, 'destroy'])->whereNumber('id');

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

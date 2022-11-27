<?php

use App\Domain\Messages\Controllers\DailyMessagingController;

Route::prefix('api/messages/')
    ->group(function () {
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

        Route::get('daily/{type}/tst', [DailyMessagingController::class, 'tst']);
    });

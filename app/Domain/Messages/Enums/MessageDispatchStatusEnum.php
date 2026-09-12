<?php

namespace App\Domain\Messages\Enums;

enum MessageDispatchStatusEnum: int
{
    /**
     * Строка создана и ждёт следующего запуска массовой рассылки
     * (см. DailyMessagingController — заберёт всё в статусе wait/1 и отправит через MTS).
     */
    case wait = 1;

    /**
     * Массовая рассылка успешно передала сообщение шлюзу
     * (DailyMessagingUpdateStatusService::massSendingSuccess()).
     */
    case sent = 2;

    /**
     * Отправлено сразу, в обход очереди массовой рассылки — оператором вручную,
     * одним сообщением из профиля клиента (MessageSendController::send()).
     */
    case operator = 3;

    public function label(): string
    {
        return match ($this) {
            self::wait => 'Waiting for the daily batch',
            self::sent => 'Sent via daily batch',
            self::operator => 'Sent by operator',
        };
    }
}

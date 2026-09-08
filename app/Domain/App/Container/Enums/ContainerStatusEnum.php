<?php

namespace App\Domain\App\Container\Enums;

/**
 * Backing value = сам id из REV_CONST.boxStatus* — для id -> case работают нативные
 * ContainerStatusEnum::from()/tryFrom(). label() — только для отображения (строка
 * "Sent"/"Stopped"/... в ContainerResource и на фронтенде).
 */
enum ContainerStatusEnum: int
{
    case temporary = -1;
    case stopped = 1;
    case in_progress = 2;
    case error = 3;
    case canceled = 4;
    case assembling = 50;
    case ready = 45;
    case sent = 70;

    public function label(): string
    {
        return match ($this) {
            self::temporary => 'Temporary',
            self::stopped => 'Stopped',
            self::in_progress => 'InProgress',
            self::error => 'Error',
            self::canceled => 'Canceled',
            self::assembling => 'Assembling',
            self::ready => 'Ready',
            self::sent => 'Sent',
        };
    }
}

<?php

namespace App\Domain\Messages\Enums;

enum MessageTypeEnum: int
{
    case sms = 1;
    case email = 2;

    public function label(): string
    {
        return match ($this) {
            self::sms => 'SMS',
            self::email => 'EMAIL',
        };
    }
}

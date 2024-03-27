<?php

namespace App\Domain\Messages\Enums;

enum MessageTypeEnum: string
{
    case sms = 'sms';
    case email = 'email';
}

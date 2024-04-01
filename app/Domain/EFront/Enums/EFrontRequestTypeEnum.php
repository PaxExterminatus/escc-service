<?php

namespace App\Domain\EFront\Enums;

enum EFrontRequestTypeEnum: string
{
    case data = 'data';
    case code = 'code';
    case reset = 'reset';
}

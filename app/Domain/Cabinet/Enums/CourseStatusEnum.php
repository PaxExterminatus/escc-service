<?php

namespace App\Domain\Cabinet\Enums;

enum CourseStatusEnum: string
{
    case active = 'active';
    case notActive = 'not active';
    case error = 'error';
    case finished = 'finished';
    case outage = 'outage';
    case finishing = 'finishing';
    case refusing = 'refusing';
    case unknown = 'unknown';
}

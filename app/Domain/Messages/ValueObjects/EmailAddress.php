<?php

namespace App\Domain\Messages\ValueObjects;

class EmailAddress
{
    public static function isValid(?string $raw): bool
    {
        if ($raw === null) {
            return false;
        }

        return filter_var($raw, FILTER_VALIDATE_EMAIL) !== false;
    }
}

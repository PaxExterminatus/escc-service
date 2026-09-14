<?php

namespace App\Domain\Messages\ValueObjects;

class BelarusMobilePhone
{
    public static function isValid(?string $raw): bool
    {
        if ($raw === null) {
            return false;
        }

        return preg_match('/^375\d{9}$/', ltrim($raw, '+')) === 1;
    }
}

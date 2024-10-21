<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum SexEnum: string
{
    case man = 'man';
    case woman = 'woman';
    case unknown = 'unknown';

    static function name(int $id): string
    {
        if ($id === 1) return SexEnum::man->value;
        if ($id === 0) return SexEnum::woman->value;
        return SexEnum::unknown->value;
    }

    static function id(int $name): ?int
    {
        $lower = strtolower($name);

        if ($lower === strtolower(SexEnum::man->value)) return 1;
        if ($lower === strtolower(SexEnum::woman->value)) return 0;
        else return null;
    }
}

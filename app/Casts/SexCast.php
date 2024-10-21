<?php

namespace App\Casts;

use App\Enums\SexEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SexCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ((int)$value === 1) return SexEnum::man->value;
        else if ((int)$value === 0) return SexEnum::woman->value;
        else return SexEnum::unknown->value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if (Str::lower($value) === SexEnum::man->value) return 1;
        else if (Str::lower($value) === SexEnum::woman->value) return 0;
        else return null;
    }
}

<?php

namespace App\Casts;

use App\Enums\SexEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SexCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): string
    {
        return SexEnum::name((int)$value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return SexEnum::id($value);
    }
}

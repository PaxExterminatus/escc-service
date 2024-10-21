<?php

namespace App\Domain\App\Container\Casts;

use App\Domain\App\Container\Enums\ContainerStatusEnum;
use Illuminate\Database\Eloquent\Model;

class ContainerStatusCast
{
    public function get(Model $model, string $key, mixed $value, array $attributes): string
    {
        return ContainerStatusEnum::name((int)$value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return ContainerStatusEnum::id($value);
    }
}

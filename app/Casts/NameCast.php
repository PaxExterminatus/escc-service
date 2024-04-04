<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NameCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        return Str::of($value)->lower()->ucfirst();
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        return Str::of($value)->upper();
    }
}

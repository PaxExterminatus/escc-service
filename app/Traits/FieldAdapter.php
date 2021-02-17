<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

trait FieldAdapter {
    function adaptCase($value): Stringable
    {
        return Str::of($value)->lower()->ucfirst();
    }
}

<?php

namespace App\Base\Repositories;

use Illuminate\Support\Facades\Storage;

abstract class JsonReader
{
    static function make(): static
    {
        return new static;
    }

    protected function read(string $filename): array
    {
        /** @var object $data */
        $path = Storage::path("dev/$filename.json");
        $content = file_get_contents($path);
        return json_decode($content, true);
    }
}

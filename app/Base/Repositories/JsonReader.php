<?php

namespace App\Base\Repositories;

use Illuminate\Support\Facades\Storage;

class JsonReader
{
    static function make(): static
    {
        return new static;
    }

    public function read(string $filename): array
    {
        /** @var object $data */
        $path = Storage::path("dev/$filename.json");
        $content = file_get_contents($path);
        return json_decode($content, true);
    }
}

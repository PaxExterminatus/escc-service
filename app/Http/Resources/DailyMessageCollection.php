<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DailyMessageCollection extends ResourceCollection
{
    public static $wrap = 'messages';

    public function toArray(Request $request): array
    {
        return $this->collection->toArray();
    }
}

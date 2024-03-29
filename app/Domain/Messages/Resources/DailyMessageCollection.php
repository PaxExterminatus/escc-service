<?php

namespace App\Domain\Messages\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DailyMessageCollection extends ResourceCollection
{
    public static $wrap = 'messages';

    public function toArray(Request $request): array
    {
        return $this->collection->toArray();
    }

    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Scan info retrieved successfully',
        ];
    }
}

<?php

namespace App\Domain\Cabinet\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Cabinet\Models\Client
 */
class ClientResource extends JsonResource
{
    public static $wrap = 'client';

    public function toArray(Request $request): array
    {
        return [
            'id' => (int)$this->id,
            'name' => $this->name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'courses' => CourseResource::collection($this->courses),
        ];
    }
}

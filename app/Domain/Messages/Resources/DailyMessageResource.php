<?php

namespace App\Domain\Messages\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Messages\Models\DailyMessage
 */
class DailyMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int)$this->id,
            'type' => $this->type,
            'address' => $this->address,
            'body' => $this->body,
        ];
    }
}


<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DailyMessage
 */
class DailyMessageResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => (int)$this->id,
            'type' => $this->type,
            'address' => $this->address,
            'body' => $this->body,
        ];
    }
}


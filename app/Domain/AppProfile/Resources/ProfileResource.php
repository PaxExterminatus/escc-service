<?php

namespace App\Domain\AppProfile\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\AppProfile\Models\Profile
 */
class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int)$this->client_id,
            'name' => $this->client_name,
            'name_middle' => $this->client_middle_name,
            'name_last' => $this->client_last_name,
        ];
    }
}

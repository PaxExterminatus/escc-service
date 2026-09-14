<?php

namespace App\Domain\Messages\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Messages\Models\MessageTemplate
 */
class MessageTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->template_id,
            'code' => $this->code,
            'name' => $this->name,
            'body' => $this->body,
            'is_active' => (bool) $this->is_active,
        ];
    }
}

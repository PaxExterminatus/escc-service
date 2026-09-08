<?php

namespace App\Domain\App\Container\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\App\Container\Models\Container
 */
class ContainerResource extends JsonResource
{
    public static $wrap = 'container';

    public function toArray(Request $request): array
    {
        return [
            'id' => (int)$this->container_id,
            'client_id' => (int)$this->client_id,
            'code' => $this->container_code,
            'status' => $this->status_id, // строка-имя статуса, см. ContainerStatusCast/ContainerStatusEnum
            'created_at' => $this->container_date?->format('d.m.Y H:i'),
        ];
    }
}

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
            'sub_id' => $this->sub_id !== null ? (int)$this->sub_id : null,
            'code' => $this->container_code,
            'status' => $this->status_id, // строка-имя статуса, см. ContainerStatusCast/ContainerStatusEnum
            'created_at' => $this->container_date?->format('d.m.Y H:i'),
            'send_date' => $this->send_date?->format('d.m.Y H:i'),
            // См. ContainerController::withCommunication — куда реально уйдёт счёт и есть ли
            // согласие клиента, до того как оператор нажмёт "Email".
            'client_email' => $this->client_email,
            'email_allowed' => (bool) $this->email_allowed,
        ];
    }
}

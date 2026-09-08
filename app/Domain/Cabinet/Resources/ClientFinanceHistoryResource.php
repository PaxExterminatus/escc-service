<?php

namespace App\Domain\Cabinet\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Cabinet\Models\ClientFinanceHistory
 */
class ClientFinanceHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int)$this->id,
            'client_id' => (int)$this->client_id,
            'operation_type' => $this->operation_type,
            'operation_date' => $this->operation_date?->toDateTimeString(),
            'amount' => $this->amount,
            'description' => $this->description,
        ];
    }
}

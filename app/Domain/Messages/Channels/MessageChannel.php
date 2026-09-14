<?php

namespace App\Domain\Messages\Channels;

use App\Domain\Messages\Enums\MessageTypeEnum;
use App\Domain\Messages\Models\ClientCommunication;

interface MessageChannel
{
    public function code(): string;

    public function label(): string;

    public function type(): MessageTypeEnum;

    public function address(ClientCommunication $communication): ?string;

    public function isAllowed(ClientCommunication $communication): bool;

    /**
     * @param array{extra_id?: string, subject?: string} $context
     * @return array{status: int, reason: string}
     */
    public function send(string $address, string $body, array $context = []): array;
}

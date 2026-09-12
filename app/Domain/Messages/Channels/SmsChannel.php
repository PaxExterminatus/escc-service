<?php

namespace App\Domain\Messages\Channels;

use App\Domain\Messages\Enums\MessageTypeEnum;
use App\Domain\Messages\Models\ClientCommunication;
use App\Domain\Messages\Services\Senders\MobileTeleSystems\MobileTeleSystemsProvider;
use App\Domain\Messages\ValueObjects\BelarusMobilePhone;

class SmsChannel implements MessageChannel
{
    public function code(): string
    {
        return 'sms';
    }

    public function label(): string
    {
        return 'SMS';
    }

    public function type(): MessageTypeEnum
    {
        return MessageTypeEnum::sms;
    }

    public function address(ClientCommunication $communication): ?string
    {
        return $communication->client_mphone;
    }

    public function isAllowed(ClientCommunication $communication): bool
    {
        return $communication->client_smsuse && BelarusMobilePhone::isValid($communication->client_mphone);
    }

    public function send(string $address, string $body, array $context = []): array
    {
        return MobileTeleSystemsProvider::make()->sendOne(
            $address,
            $body,
            $context['extra_id'] ?? '',
            env('MTS_API_ALFA_NAME'),
        );
    }
}

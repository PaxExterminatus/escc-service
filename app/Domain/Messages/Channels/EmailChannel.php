<?php

namespace App\Domain\Messages\Channels;

use App\Domain\Messages\Enums\MessageTypeEnum;
use App\Domain\Messages\Models\ClientCommunication;
use App\Domain\Messages\Services\Senders\Mail\MailProvider;
use App\Domain\Messages\ValueObjects\EmailAddress;

class EmailChannel implements MessageChannel
{
    public function code(): string
    {
        return 'email';
    }

    public function label(): string
    {
        return 'Email';
    }

    public function type(): MessageTypeEnum
    {
        return MessageTypeEnum::email;
    }

    public function address(ClientCommunication $communication): ?string
    {
        return $communication->client_email;
    }

    public function isAllowed(ClientCommunication $communication): bool
    {
        return $communication->subscriber_email_status && EmailAddress::isValid($communication->client_email);
    }

    public function send(string $address, string $body, array $context = []): array
    {
        return MailProvider::make()->sendOne($address, $context['subject'] ?? 'Сообщение', $body);
    }
}

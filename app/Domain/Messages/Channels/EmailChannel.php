<?php

namespace App\Domain\Messages\Channels;

use App\Domain\Messages\Enums\MessageTypeEnum;
use App\Domain\Messages\Models\ClientCommunication;
use App\Domain\Messages\Services\Senders\Mail\MailProvider;
use App\Domain\Messages\ValueObjects\EmailAddress;

class EmailChannel implements MessageChannel
{
    /** Тема письма, когда шаблон не выбран и своей темы нет */
    public const DEFAULT_SUBJECT = 'Сообщение';

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

    /**
     * $body приходит уже собранным html (см. EmailComposer::composeFromText) — отправляем
     * как html, иначе получатель увидит исходник разметки вместо письма.
     */
    public function send(string $address, string $body, array $context = []): array
    {
        return MailProvider::make()->sendHtml($address, $context['subject'] ?? self::DEFAULT_SUBJECT, $body);
    }
}

<?php

namespace App\Domain\Messages\Services\MobileTeleSystems;

use JsonSerializable;

class Batch implements JsonSerializable
{
    /** @var BatchMessage[] */
    protected array $message = [];

    static function make(): static
    {
        return new static;
    }

    function addMessage(BatchMessage $message): static
    {
        $this->message[] = $message;
        return $this;
    }

    function toArray(): array
    {
        return [
            'messages' => $this->message,
        ];
    }

    function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

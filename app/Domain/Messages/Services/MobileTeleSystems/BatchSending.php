<?php

namespace App\Domain\Messages\Services\MobileTeleSystems;

class BatchSending
{
    /** @var BatchSendingMessage[] */
    protected array $message = [];

    static function make(): static
    {
        return new static;
    }

    function addMessage(BatchSendingMessage $message): static
    {
        $this->message[] = $message;
        return $this;
    }
}

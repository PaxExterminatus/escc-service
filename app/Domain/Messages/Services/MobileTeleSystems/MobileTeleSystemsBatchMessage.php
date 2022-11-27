<?php

namespace App\Domain\Messages\Services\MobileTeleSystems;

use JsonSerializable;

class MobileTeleSystemsBatchMessage implements JsonSerializable
{
    protected string $phoneNumber;
    protected string $extraId;
    protected string $text;

    static function make(): static
    {
        return new static;
    }

    function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    function setExtraId(string $extraId): static
    {
        $this->extraId = $extraId;
        return $this;
    }

    function setText(string $text): static
    {
        $this->text = $text;
        return $this;
    }

    function toArray(): array
    {
        return [
            'phone_number' => $this->phoneNumber,
            'extra_id' => $this->extraId,
            'text' => $this->text,
        ];
    }

    function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

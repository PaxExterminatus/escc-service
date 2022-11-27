<?php

namespace App\Domain\Messages\Services\Senders\MobileTeleSystems;

use JsonSerializable;

class MobileTeleSystemsBatchChanel implements JsonSerializable
{
    protected string $name;
    protected string $alphaName;
    protected int $ttl;

    static function make(): static
    {
        return new static;
    }

    function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    function getName(): string
    {
        return $this->name;
    }

    function setOptionAlphaName(string $alphaName): static
    {
        $this->alphaName = $alphaName;
        return $this;
    }

    function setOptionTtl(int $ttl): static
    {
        $this->ttl = $ttl;
        return $this;
    }

    function options(): array
    {
        return [
            'alpha_name' => $this->alphaName,
            'ttl' => $this->ttl,
        ];
    }

    function toArray(): array
    {
        return $this->options();
    }

    function jsonSerialize()
    {
        return $this->toArray();
    }
}

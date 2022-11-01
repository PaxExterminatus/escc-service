<?php

namespace App\Domain\Messages\Queries;

trait DailyMessagesParamsTrait
{
    static string $TYPE_SMS = 'SMS';
    static string $TYPE_EMAIL = 'EMAIL';

    protected ?string $type = null;

    function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    function setTypeAsSms(): static
    {
        return $this->setType(static::$TYPE_SMS);
    }

    function setTypeAsEmail(): static
    {
        return $this->setType(static::$TYPE_EMAIL);
    }
}

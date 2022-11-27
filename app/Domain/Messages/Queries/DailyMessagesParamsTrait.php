<?php

namespace App\Domain\Messages\Queries;

use App\Domain\Messages\Models\DailyMessage;

trait DailyMessagesParamsTrait
{
    protected ?string $type = null;

    function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    function setTypeAsSms(): static
    {
        return $this->setType(DailyMessage::$TYPE_SMS);
    }

    function setTypeAsEmail(): static
    {
        return $this->setType(DailyMessage::$TYPE_EMAIL);
    }
}

<?php

namespace App\Domain\Messages\Queries;

trait DailyMessagesParamsTrait
{
    protected string $type;

    function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }
}

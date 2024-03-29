<?php

namespace App\Domain\Messages\Queries;

use App\Domain\Messages\Models\DailyMessage;

trait DailyMessagesParamsTrait
{
    protected string $type;

    function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }
}

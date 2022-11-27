<?php

namespace App\Domain\Messages\Queries;

use App\Domain\Messages\Models\DailyMessage;

interface DailyMessagesRepository
{
    function setType(string $type): static;
    function setTypeAsSms(): static;
    function setTypeAsEmail(): static;

    /**
     * @return iterable|DailyMessage[]
     */
    function get(): iterable;
}

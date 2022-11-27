<?php

namespace App\Domain\Messages\Queries;

use App\Domain\Messages\Models\DailyMessageView;

interface DailyMessagesRepository
{
    function setType(string $type): static;
    function setTypeAsSms(): static;
    function setTypeAsEmail(): static;

    /**
     * @return iterable|DailyMessageView[]
     */
    function get(): iterable;
}

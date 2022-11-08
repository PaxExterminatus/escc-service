<?php

namespace App\Domain\Messages\Queries;

use App\Domain\Messages\Models\MessagesDaily;

interface DailyMessagesRepository
{
    function setType(string $type): static;
    function setTypeAsSms(): static;
    function setTypeAsEmail(): static;

    /**
     * @return iterable|MessagesDaily[]
     */
    function get(): iterable;
}

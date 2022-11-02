<?php

namespace App\Domain\Messages\Queries;

interface DailyMessagesRepository
{
    function setType(string $type): static;
    function setTypeAsSms(): static;
    function setTypeAsEmail(): static;

    function get(): iterable;
}

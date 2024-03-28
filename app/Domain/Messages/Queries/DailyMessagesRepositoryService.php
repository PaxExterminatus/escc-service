<?php

namespace App\Domain\Messages\Queries;

class DailyMessagesRepositoryService
{
    static function build(): DailyMessagesJsonReader|DailyMessagesQuery
    {
        if (USE_DEVELOPMENT_REPOSITORY_FOR_ALL || USE_DEVELOPMENT_REPOSITORY_FOR_DAILY_MESSAGES)
            return new DailyMessagesJsonReader;
        else
            return new DailyMessagesQuery;
    }
}

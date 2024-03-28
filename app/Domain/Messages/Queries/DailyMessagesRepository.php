<?php

namespace App\Domain\Messages\Queries;

class DailyMessagesRepository
{
    static function get(): DailyMessagesSourceJson|DailyMessagesSourceDatabase
    {
        if (USE_DEVELOPMENT_REPOSITORY_FOR_ALL)
            return new DailyMessagesSourceJson;
        if (USE_DEVELOPMENT_REPOSITORY_FOR_DAILY_MESSAGES)
            return new DailyMessagesSourceJson;

        return new DailyMessagesSourceDatabase;
    }
}

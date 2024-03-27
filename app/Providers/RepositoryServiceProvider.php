<?php

namespace App\Providers;

use App\Domain\Messages\Queries\DailyMessagesQuery;
use App\Domain\Messages\Queries\DailyMessagesReader;
use App\Domain\Messages\Queries\DailyMessagesRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    function register(): void
    {
        $this->app->bind(
            DailyMessagesRepository::class,
            USE_DEVELOPMENT_REPOSITORY_FOR_ALL || USE_DEVELOPMENT_REPOSITORY_FOR_DAILY_MESSAGES
                ? DailyMessagesReader::class
                : DailyMessagesQuery::class
        );
    }
}

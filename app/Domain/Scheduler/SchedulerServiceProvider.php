<?php

namespace App\Domain\Scheduler;

use Illuminate\Support\ServiceProvider;

class SchedulerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}

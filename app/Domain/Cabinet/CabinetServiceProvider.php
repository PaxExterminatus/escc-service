<?php

namespace App\Domain\Cabinet;

use Illuminate\Support\ServiceProvider;

class CabinetServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}

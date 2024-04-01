<?php

namespace App\Domain\EFront;

use Illuminate\Support\ServiceProvider;

class EFrontServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}

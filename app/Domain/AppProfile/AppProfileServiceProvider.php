<?php

namespace App\Domain\AppProfile;

use Illuminate\Support\ServiceProvider;

class AppProfileServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
    }
}

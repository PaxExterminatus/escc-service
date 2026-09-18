<?php

namespace App\Domain\Templates;

use Illuminate\Support\ServiceProvider;

class TemplatesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
    }
}

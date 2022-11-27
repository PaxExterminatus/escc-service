<?php

namespace App\Domain\Messages;

use Illuminate\Support\ServiceProvider;

class MessagesServiceProviders extends ServiceProvider
{
    function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }
}

<?php

namespace App\Domain\App\Invoice;

use App\Domain\App\Invoice\Console\GenerateInvoiceTemplateCommand;
use Illuminate\Support\ServiceProvider;

class AppInvoiceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateInvoiceTemplateCommand::class,
            ]);
        }
    }
}

<?php

namespace App\Domain\App\Invoice\Console;

use App\Domain\App\Invoice\Services\InvoiceTemplateBuilder;
use Illuminate\Console\Command;

/**
 * Пересобирает тестовый шаблон счёта (resources/templates/invoice.docx) из кода —
 * до появления реального бланка от заказчика этот шаблон и есть источник истины.
 */
class GenerateInvoiceTemplateCommand extends Command
{
    protected $signature = 'invoice:template:generate';

    protected $description = 'Пересобрать тестовый шаблон счёта resources/templates/invoice.docx';

    public function handle(InvoiceTemplateBuilder $builder): int
    {
        $path = resource_path('templates/invoice.docx');

        $builder->build($path);

        $this->info("Шаблон сохранён: {$path}");

        return self::SUCCESS;
    }
}

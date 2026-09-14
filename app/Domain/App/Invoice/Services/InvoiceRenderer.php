<?php

namespace App\Domain\App\Invoice\Services;

use App\Domain\App\Container\Models\Container;
use App\Domain\App\Profile\Models\Profile;
use Illuminate\Support\Collection;
use PhpOffice\PhpWord\TemplateProcessor;

class InvoiceRenderer
{
    /**
     * Заполняет resources/templates/invoice.docx данными по каждому контейнеру
     * (один клон блока ${invoices} на контейнер) и сохраняет результат во временный файл.
     *
     * @param Collection<int, Container> $containers
     * @return string путь к сгенерированному .docx во временной директории
     */
    public function render(Collection $containers): string
    {
        $profiles = Profile::whereIn('client_id', $containers->pluck('client_id')->unique())
            ->get()
            ->keyBy('client_id');

        $processor = new TemplateProcessor(resource_path('templates/invoice.docx'));
        $processor->cloneBlock('invoices', $containers->count(), true, true);

        foreach ($containers->values() as $i => $container) {
            $index = $i + 1;
            $profile = $profiles->get($container->client_id);

            $processor->setValue("invoice_number#{$index}", (string) $container->container_id);
            $processor->setValue("invoice_date#{$index}", $container->send_date?->format('d.m.Y') ?? '—');
            $processor->setValue("client_code#{$index}", $profile?->client_code ?? '—');
            $processor->setValue("client_name#{$index}", $this->clientName($profile));
            $processor->setValue("item_name#{$index}", "Оплата за посылку №{$container->container_id}");
            $processor->setValue("item_amount#{$index}", $this->formatAmount($container->container_cost));
            $processor->setValue("total_amount#{$index}", $this->formatAmount($container->container_cost));
        }

        $path = tempnam(sys_get_temp_dir(), 'invoice_') . '.docx';
        $processor->saveAs($path);

        return $path;
    }

    protected function clientName(?Profile $profile): string
    {
        if (!$profile) {
            return '—';
        }

        return trim("{$profile->client_last_name} {$profile->client_name} {$profile->client_middle_name}");
    }

    protected function formatAmount(float $amount): string
    {
        return number_format($amount, 2, ',', ' ');
    }
}

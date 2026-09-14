<?php

namespace App\Domain\App\Invoice\Controllers;

use App\Domain\App\Container\Enums\ContainerStatusEnum;
use App\Domain\App\Container\Models\Container;
use App\Domain\App\Invoice\Services\InvoiceRenderer;
use App\Domain\App\Profile\Models\Profile;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoiceController extends Controller
{
    /**
     * Счёт по одному контейнеру
     *
     * Печать доступна только для отправленных контейнеров (REV_CONST.boxStatusSent = 70) —
     * именно на этом шаге в легаси выставляется счёт (см. документацию домена "Финансы").
     */
    public function show(int $id, InvoiceRenderer $renderer): BinaryFileResponse
    {
        $container = Container::where('container_id', $id)->firstOrFail();

        abort_if(!$this->isInvoiceable($container), 422, 'Счёт можно распечатать только для отправленного контейнера.');

        $path = $renderer->render(collect([$container]));

        return response()->download($path, "invoice-{$container->container_id}.docx")->deleteFileAfterSend();
    }

    /**
     * Обзор контейнеров за период (один день, если from === to, либо неделя)
     *
     * Группировка по send_date — дню фактической отправки, на который в легаси приходится
     * выставление счёта.
     */
    public function range(string $from, string $to): JsonResponse
    {
        $containers = $this->containersForRange($from, $to)->get();

        $profiles = Profile::whereIn('client_id', $containers->pluck('client_id')->unique())
            ->get()
            ->keyBy('client_id');

        $rows = $containers->map(function (Container $container) use ($profiles) {
            $profile = $profiles->get($container->client_id);

            return [
                'container_id' => $container->container_id,
                'client_id' => $container->client_id,
                'client_code' => $profile?->client_code,
                'client_name' => trim("{$profile?->client_last_name} {$profile?->client_name} {$profile?->client_middle_name}"),
                'container_cost' => (float) $container->container_cost,
                'send_date' => $container->send_date?->toIso8601String(),
            ];
        });

        return response()->json(['containers' => $rows]);
    }

    /**
     * Печать всех счетов за период одним файлом
     */
    public function rangePrint(string $from, string $to, InvoiceRenderer $renderer): BinaryFileResponse
    {
        $containers = $this->containersForRange($from, $to)->get();

        abort_if($containers->isEmpty(), 422, 'За этот период нет отправленных контейнеров.');

        $path = $renderer->render($containers);

        return response()->download($path, "invoices-{$from}_{$to}.docx")->deleteFileAfterSend();
    }

    protected function containersForRange(string $from, string $to)
    {
        return Container::where('status_id', ContainerStatusEnum::sent->value)
            ->whereBetween('send_date', [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ])
            ->orderBy('send_date')
            ->orderBy('container_id');
    }

    protected function isInvoiceable(Container $container): bool
    {
        return $container->status_id === ContainerStatusEnum::sent->label() && $container->send_date !== null;
    }
}

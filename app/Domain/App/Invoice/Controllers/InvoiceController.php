<?php

namespace App\Domain\App\Invoice\Controllers;

use App\Domain\App\Container\Enums\ContainerStatusEnum;
use App\Domain\App\Container\Models\Container;
use App\Domain\App\Profile\Models\Profile;
use App\Domain\Messages\Channels\EmailChannel;
use App\Domain\Messages\Models\ClientCommunication;
use App\Domain\Messages\Services\Senders\Mail\MailProvider;
use App\Domain\Templates\Enums\TemplateOperationEnum;
use App\Domain\Templates\Services\DocumentRenderer;
use App\Domain\Templates\Services\EmailComposer;
use App\Domain\Templates\Services\TagResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoiceController extends Controller
{
    /**
     * Счёт по одному контейнеру
     *
     * Печать доступна только для отправленных контейнеров (REV_CONST.boxStatusSent = 70) —
     * именно на этом шаге в легаси выставляется счёт (см. документацию домена "Финансы").
     */
    public function show(int $id, TagResolver $tags, DocumentRenderer $renderer): BinaryFileResponse
    {
        $container = Container::where('container_id', $id)->firstOrFail();

        abort_if(!$this->isInvoiceable($container), 422, 'Счёт можно распечатать только для отправленного контейнера.');

        $path = $renderer->render(TemplateOperationEnum::invoice, $this->rowsFor(collect([$container]), $tags));

        return response()->download($path, "invoice-{$container->container_id}.docx")->deleteFileAfterSend();
    }

    /**
     * Предпросмотр письма со счётом для конкретного контейнера — то же, что реально уйдёт
     * при sendEmail(), но без отправки (реальные данные клиента/счёта, обёрнутые в обёртку письма).
     */
    public function emailPreview(int $id, EmailComposer $composer): JsonResponse
    {
        $container = Container::where('container_id', $id)->firstOrFail();

        abort_if(!$this->isInvoiceable($container), 422, 'Письмо можно посмотреть только для отправленного контейнера.');

        return response()->json(['html' => $composer->composeForContainer($container)['html']]);
    }

    /**
     * Отправить счёт по email вместо печати — то же тело письма (html-шаблон операции,
     * обёрнутый в общие шапку/футер), с приложенным docx-документом счёта.
     */
    public function sendEmail(int $id, TagResolver $tags, DocumentRenderer $renderer, EmailComposer $composer): JsonResponse
    {
        $container = Container::where('container_id', $id)->firstOrFail();

        abort_if(!$this->isInvoiceable($container), 422, 'Письмо можно отправить только по отправленному контейнеру.');

        $communication = ClientCommunication::where('client_id', $container->client_id)->firstOrFail();
        $email = new EmailChannel();

        abort_if(!$email->isAllowed($communication), 422, 'Клиент не дал согласие на Email либо адрес некорректен.');

        $composed = $composer->composeForContainer($container);
        $documentPath = $renderer->render(TemplateOperationEnum::invoice, $this->rowsFor(collect([$container]), $tags));

        $result = MailProvider::make()->sendHtml(
            $email->address($communication),
            $composed['subject'],
            $composed['html'],
            $documentPath,
            "invoice-{$container->container_id}.docx"
        );

        @unlink($documentPath);

        return response()->json(['response' => $result]);
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
    public function rangePrint(string $from, string $to, TagResolver $tags, DocumentRenderer $renderer): BinaryFileResponse
    {
        $containers = $this->containersForRange($from, $to)->get();

        abort_if($containers->isEmpty(), 422, 'За этот период нет отправленных контейнеров.');

        $path = $renderer->render(TemplateOperationEnum::invoice, $this->rowsFor($containers, $tags));

        return response()->download($path, "invoices-{$from}_{$to}.docx")->deleteFileAfterSend();
    }

    /**
     * @param Collection<int, Container> $containers
     * @return array<int, array<string, string>>
     */
    protected function rowsFor(Collection $containers, TagResolver $tags): array
    {
        return $containers->map(fn (Container $container) => $tags->resolveForContainer($container))->all();
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

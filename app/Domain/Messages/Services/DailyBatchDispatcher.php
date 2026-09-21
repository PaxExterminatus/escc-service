<?php

namespace App\Domain\Messages\Services;

use App\Domain\Messages\DataService\DailyMessagesDataService;
use App\Domain\Messages\Enums\MessageTypeEnum;
use App\Domain\Messages\Services\DataManagement\DailyMessagingUpdateStatusService;
use App\Domain\Messages\Services\Senders\Mail\MailProvider;
use App\Domain\Messages\Services\Senders\MobileTeleSystems\MobileTeleSystemsProvider;
use App\Domain\Templates\Services\EmailComposer;
use Illuminate\Support\Collection;

/**
 * Отправка накопившейся дневной очереди (EMSG.EMSG_STATUS = wait) для одного канала — общая
 * точка входа что для ручной кнопки "Отправить" (DailyMessagingController::send), что для
 * планировщика (см. App\Console\Commands\SendDailyMessages), чтобы поведение не разъезжалось.
 *
 * SMS уходит одним batch-запросом в MTS (шлюз сам разбирается со всеми адресатами разом).
 * Email такого batch-API не имеет — отправляется по одному, поэтому здесь же собирается
 * частичный результат: успешные помечаются "sent", проблемные остаются в очереди со своей
 * причиной ошибки в ответе, а не проваливают всю рассылку разом.
 */
class DailyBatchDispatcher
{
    /** Тема письма дневной рассылки — у EMSG нет своего поля под тему, письма делового обихода. */
    protected const EMAIL_SUBJECT = 'Уведомление от ЕШКО';

    public function __construct(
        protected DailyMessagingUpdateStatusService $statusService,
        protected EmailComposer $composer,
    ) {
    }

    /**
     * @return array{status: int, reason: string, sent: int}
     */
    public function dispatch(MessageTypeEnum $type): array
    {
        // Всегда отправляет "просроченное на сегодня" (дата постановки в очередь <= сегодня),
        // независимо от того, какой период сейчас выбран в фильтре списка на странице — фильтр
        // там только для просмотра, на то, что реально уйдёт, не влияет.
        $messages = DailyMessagesDataService::make()->setType($type->name)->setRange(null, now()->toDateString())->get();

        if ($messages->isEmpty()) {
            return ['status' => 200, 'reason' => 'Очередь пуста', 'sent' => 0];
        }

        return $type === MessageTypeEnum::email
            ? $this->dispatchEmail($messages)
            : $this->dispatchSms($messages);
    }

    protected function dispatchSms(Collection $messages): array
    {
        $senderName = env('MTS_API_ALFA_NAME');
        $result = MobileTeleSystemsProvider::make()->massSending($messages, $senderName);
        $response = $result['response'];

        if ($response->status() === 200) {
            $this->statusService->massSendingSuccess($messages);
        }

        return [
            'status' => $response->status(),
            'reason' => $response->reason(),
            'sent' => $response->status() === 200 ? $messages->count() : 0,
        ];
    }

    /**
     * Тело в очереди хранится обычным текстом (та же форма, что и у SMS — простая правка в
     * редакторе очереди, без HTML-разметки на глазах у оператора) — в фирменную обёртку
     * оборачивается только сейчас, непосредственно перед отправкой.
     */
    protected function dispatchEmail(Collection $messages): array
    {
        $succeeded = collect();
        $failures = [];

        foreach ($messages as $message) {
            $html = $this->composer->composeFromText($message->body);
            $result = MailProvider::make()->sendHtml($message->address, self::EMAIL_SUBJECT, $html);

            if ($result['status'] === 200) {
                $succeeded->push($message);
            } else {
                $failures[] = "#{$message->id} {$message->address}: {$result['reason']}";
            }
        }

        if ($succeeded->isNotEmpty()) {
            $this->statusService->massSendingSuccess($succeeded);
        }

        return [
            // 207 Multi-Status — часть писем ушла, часть нет, обеим сторонам это важно увидеть.
            'status' => $failures ? 207 : 200,
            'reason' => $failures ? implode('; ', $failures) : 'OK',
            'sent' => $succeeded->count(),
        ];
    }
}

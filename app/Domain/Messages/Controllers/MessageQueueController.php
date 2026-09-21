<?php

namespace App\Domain\Messages\Controllers;

use App\Domain\Messages\Enums\MessageDispatchStatusEnum;
use App\Domain\Messages\Enums\MessageTypeEnum;
use App\Domain\Messages\Models\ElectronicMessage;
use App\Domain\Messages\Requests\StoreQueuedMessageRequest;
use App\Domain\Messages\Requests\UpdateQueuedMessageRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * CRUD над одной строкой дневной очереди (EMSG, EMSG_STATUS = wait) — то, чего не было у
 * DailyMessagingController (там только чтение готовой очереди целиком + запуск отправки
 * разом). Правка/удаление относятся к самой строке EMSG напрямую, поэтому не зависят от
 * того, как строку читали изначально (SMS — через легаси-view, EMAIL — напрямую, см.
 * DailyMessagesDataService) — id везде один и тот же EMSG.EMSG.
 *
 * Тело хранится обычным текстом для обоих каналов — в фирменную обёртку письмо оборачивается
 * только в момент отправки (см. DailyBatchDispatcher), не раньше, иначе оператор редактировал
 * бы кусок HTML-разметки вместо своего текста.
 */
class MessageQueueController extends Controller
{
    public function store(StoreQueuedMessageRequest $request): JsonResponse
    {
        $data = $request->validated();

        $message = ElectronicMessage::create([
            'emsg_type' => constant(MessageTypeEnum::class.'::'.$data['type']),
            'emsg_date' => now(),
            'emsg_address' => $data['address'],
            'emsg_body' => $data['body'],
            'emsg_status' => MessageDispatchStatusEnum::wait,
        ]);

        return response()->json(['id' => $message->emsg], 201);
    }

    public function update(int $id, UpdateQueuedMessageRequest $request): JsonResponse
    {
        $message = ElectronicMessage::findOrFail($id);
        $this->guardEditable($message);

        $data = $request->validated();
        $message->emsg_address = $data['address'];
        $message->emsg_body = $data['body'];
        $message->save();

        return response()->json(['id' => $message->emsg]);
    }

    public function destroy(int $id): Response
    {
        $message = ElectronicMessage::findOrFail($id);
        $this->guardEditable($message);

        $message->delete();

        return response()->noContent();
    }

    /** Отправленное сообщение уже ушло получателю — трогать его тут больше нельзя */
    protected function guardEditable(ElectronicMessage $message): void
    {
        abort_if(
            $message->emsg_status !== MessageDispatchStatusEnum::wait,
            422,
            'Сообщение уже отправлено — изменить или удалить нельзя.'
        );
    }
}

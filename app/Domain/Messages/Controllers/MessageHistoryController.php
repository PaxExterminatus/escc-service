<?php

namespace App\Domain\Messages\Controllers;

use App\Domain\Messages\Models\ClientCommunication;
use App\Domain\Messages\Models\ElectronicMessage;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

/**
 * История уведомлений (SMS/Email) клиента — EMSG не привязана к клиенту напрямую (нет
 * CLIENT_ID, адресация по EMSG_ADDRESS), поэтому подбираем по телефону/email из
 * CLIENT_PROPERTY. Показывает всё: и уже отправленное (сразу оператором или массовой
 * рассылкой), и то, что ещё стоит в очереди на отправку (см. MessageDispatchStatusEnum).
 */
class MessageHistoryController extends Controller
{
    public function forClient(int $clientId): JsonResponse
    {
        $communication = ClientCommunication::where('client_id', $clientId)->first();

        $addresses = array_filter([$communication?->client_mphone, $communication?->client_email]);

        if (empty($addresses)) {
            return response()->json(['messages' => []]);
        }

        $messages = ElectronicMessage::whereIn('emsg_address', $addresses)
            ->orderByDesc('emsg_date')
            ->orderByDesc('emsg')
            ->limit(100)
            ->get();

        return response()->json(['messages' => $messages->map(fn (ElectronicMessage $message) => [
            'id' => $message->emsg,
            'type' => $message->emsg_type->label(),
            'address' => $message->emsg_address,
            'body' => $message->emsg_body,
            'status' => $message->emsg_status->label(),
            'date' => Carbon::parse($message->emsg_date)->format('d.m.Y H:i'),
        ])]);
    }
}

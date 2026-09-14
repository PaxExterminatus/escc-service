<?php

namespace App\Domain\Messages\Controllers;

use App\Domain\Messages\Channels\MessageChannelRegistry;
use App\Domain\Messages\Enums\MessageDispatchStatusEnum;
use App\Domain\Messages\Models\ClientCommunication;
use App\Domain\Messages\Models\ElectronicMessage;
use App\Domain\Messages\Models\MessageTemplate;
use App\Domain\Messages\Requests\SendMessageRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class MessageSendController extends Controller
{
    /** Recipient status for a client: address + allowed flag, per channel */
    public function recipient(int $clientId): JsonResponse
    {
        $communication = ClientCommunication::where('client_id', $clientId)->firstOrFail();

        $channels = collect(MessageChannelRegistry::all())->map(fn ($channel) => [
            'code' => $channel->code(),
            'label' => $channel->label(),
            'address' => $channel->address($communication),
            'allowed' => $channel->isAllowed($communication),
        ]);

        return response()->json(['channels' => $channels]);
    }

    /** Send one message to a client over any registered channel */
    public function send(SendMessageRequest $request): JsonResponse
    {
        $data = $request->validated();

        $channel = MessageChannelRegistry::find($data['channel']);
        $communication = ClientCommunication::where('client_id', $data['client_id'])->firstOrFail();

        abort_if(!$channel->isAllowed($communication), 422, "Client has not opted in for {$channel->label()} or has no valid address.");

        $body = $data['body'] ?? null;
        $template = null;

        if (!empty($data['template_id'])) {
            $template = MessageTemplate::where('template_id', $data['template_id'])->firstOrFail();
            $body = $template->render($data['params'] ?? []);
        }

        $address = $channel->address($communication);

        $message = ElectronicMessage::create([
            'emsg_type' => $channel->type(),
            'emsg_date' => now(),
            'emsg_address' => $address,
            'emsg_body' => $body,
            'emsg_status' => MessageDispatchStatusEnum::operator,
        ]);

        $result = $channel->send($address, $body, [
            'extra_id' => (string) $message->emsg,
            'subject' => $template?->name ?? 'Сообщение',
        ]);

        return response()->json([
            'emsg_id' => $message->emsg,
            'response' => $result,
        ]);
    }
}

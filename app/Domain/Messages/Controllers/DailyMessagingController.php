<?php

namespace App\Domain\Messages\Controllers;

use App\Domain\Messages\Enums\MessageTypeEnum;
use App\Domain\Messages\Queries\DailyMessagesRepository;
use App\Domain\Messages\Queries\DailyMessagesSourceDatabase;
use App\Domain\Messages\Queries\DailyMessagesSourceJson;
use App\Domain\Messages\Requests\DailyMessagesRequest;
use App\Domain\Messages\Services\DataManagement\DailyMessagingUpdateStatusService;
use App\Domain\Messages\Services\Senders\MobileTeleSystems\MobileTeleSystemsProvider;
use App\Http\Controllers\Controller;
use App\Http\Resources\DailyMessageCollection;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Daily Messaging
 * @tags Client Messaging, SMS
 *
 */
class DailyMessagingController extends Controller
{
    protected DailyMessagingUpdateStatusService $statusService;
    protected DailyMessagesSourceDatabase|DailyMessagesSourceJson $dailyMessagesSource;

    public function __construct()
    {
        $this->statusService = new DailyMessagingUpdateStatusService();
        $this->dailyMessagesSource = DailyMessagesRepository::get();
    }

    /**
     * Daily Messaging: get list
     * @example /api/messages/daily/sms
     * @example /api/messages/daily/email
     *

     */
    public function index(DailyMessagesRequest $request): Responsable
    {
        $messages = $this->applyParamsToDailyMessagesRepository($request->type)->get();
        $resource = new DailyMessageCollection($messages);

        return $resource;
    }

    /**
     * Daily Messaging: send
     * @example /api/messages/daily/sms/send
     * @example /api/messages/daily/email/send
     */
    public function send(DailyMessagesRequest $request): JsonResponse
    {
        $messages = $this->applyParamsToDailyMessagesRepository($request->type)->get();

        $result = MobileTeleSystemsProvider::make()->massSending($messages, $this->senderName());

        $request = $result['request'];
        $response = $result['response'];

        if ($response->status() === 200)
        {
            $this->statusService->massSendingSuccess($messages);
        }

        return response()->json([
            'response' => [
                'status' => $response->status(),
                'reason' => $response->reason(),
                'data' => $response->json(),
            ],
            'request' => $request,
        ]);
    }

    /**
     * Daily Messaging: get list as txt file
     * @example /api/messages/daily/sms/txt
     * @example /api/messages/daily/email/txt
     */
    public function txt(DailyMessagesRequest $request): Response|Application|ResponseFactory
    {
        $messages = $this->applyParamsToDailyMessagesRepository($request->type)->get();

        $content = "Phone number\t1\r\n";

        foreach ($messages as $message)
        {
            $content = $content . "$message->address\t$message->body\r\n";
        }

        $filename = $this->senderName();

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-store, no-cache',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }

    protected function applyParamsToDailyMessagesRepository(string $type): DailyMessagesSourceJson|DailyMessagesSourceDatabase
    {
        if (Str::lower($type) === MessageTypeEnum::sms->value)
            $this->dailyMessagesSource->setType(MessageTypeEnum::sms->value);

        if (Str::lower($type) === MessageTypeEnum::email->value)
            $this->dailyMessagesSource->setType(MessageTypeEnum::email->value);

        return $this->dailyMessagesSource;
    }

    protected function senderName(): string
    {
        return env('MTS_API_ALFA_NAME');
    }
}

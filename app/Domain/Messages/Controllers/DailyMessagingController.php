<?php

namespace App\Domain\Messages\Controllers;

use App\Domain\Messages\Enums\MessageTypeEnum;
use App\Domain\Messages\Queries\DailyMessagesJsonReader;
use App\Domain\Messages\Queries\DailyMessagesQuery;
use App\Domain\Messages\Queries\DailyMessagesRepositoryService;
use App\Domain\Messages\Requests\DailyMessagesRequest;
use App\Domain\Messages\Services\DataManagement\DailyMessagingUpdateStatusService;
use App\Domain\Messages\Services\Senders\MobileTeleSystems\MobileTeleSystemsProvider;
use App\Http\Controllers\ApiController;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class DailyMessagingController extends ApiController
{
    protected DailyMessagingUpdateStatusService $statusService;
    protected DailyMessagesQuery|DailyMessagesJsonReader $dailyRepository;

    public function __construct()
    {
        $this->statusService = new DailyMessagingUpdateStatusService();
        $this->dailyRepository = DailyMessagesRepositoryService::build();
    }

    /**
     * Daily messages: list
     */
    public function index(DailyMessagesRequest $request): JsonResponse
    {
        $messages = $this->applyParamsToDailyMessagesRepository($request->type)->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /**
     * Daily messages: send
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
     * Daily messages: txt file
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

    protected function applyParamsToDailyMessagesRepository(string $type): DailyMessagesJsonReader|DailyMessagesQuery
    {
        if (Str::lower($type) === MessageTypeEnum::sms->value)
            $this->dailyRepository->setTypeAsSms();

        if (Str::lower($type) === MessageTypeEnum::email->value)
            $this->dailyRepository->setTypeAsEmail();

        return $this->dailyRepository;
    }

    protected function senderName(): string
    {
        return env('MTS_API_ALFA_NAME');
    }
}

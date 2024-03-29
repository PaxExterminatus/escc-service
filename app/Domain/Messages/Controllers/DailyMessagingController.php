<?php

namespace App\Domain\Messages\Controllers;

use App\Domain\Messages\Models\DailyMessageView;
use App\Domain\Messages\Queries\DailyMessagesRepository;
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
    protected DailyMessagesRepository $dailyRepository;
    protected DailyMessagingUpdateStatusService $statusService;

    function __construct(DailyMessagesRepository $dailyRepository)
    {
        $this->dailyRepository = $dailyRepository;
        $this->statusService = new DailyMessagingUpdateStatusService();
    }

    /** Get list of daily messages */
    function index(string $type): JsonResponse
    {
        $messages = $this->applyParamsToDailyMessagesRepository($type)->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /** Send daily messages */
    function send(string $type): JsonResponse
    {
        $messages = $this->applyParamsToDailyMessagesRepository($type)->get();

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

    function txt(string $type): Response|Application|ResponseFactory
    {
        $messages = $this->applyParamsToDailyMessagesRepository($type)->get();

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

    protected function applyParamsToDailyMessagesRepository(string $type): DailyMessagesRepository
    {
        if (Str::upper($type) === DailyMessageView::$TYPE_SMS)
            $this->dailyRepository->setTypeAsSms();

        if (Str::upper($type) === DailyMessageView::$TYPE_EMAIL)
            $this->dailyRepository->setTypeAsEmail();

        return $this->dailyRepository;
    }

    protected function senderName(): string
    {
        return env('MTS_API_ALFA_NAME');
    }
}

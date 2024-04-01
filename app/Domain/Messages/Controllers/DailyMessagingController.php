<?php

namespace App\Domain\Messages\Controllers;

use App\Domain\Messages\DataService\DailyMessagesDataService;
use App\Domain\Messages\Requests\DailyMessagesRequest;
use App\Domain\Messages\Resources\DailyMessageCollection;
use App\Domain\Messages\Services\DataManagement\DailyMessagingUpdateStatusService;
use App\Domain\Messages\Services\Senders\MobileTeleSystems\MobileTeleSystemsProvider;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Daily Messaging
 * @tags Messaging, SMS, Emailing
 */
class DailyMessagingController extends Controller
{
    protected DailyMessagingUpdateStatusService $statusService;

    public function __construct()
    {
        $this->statusService = new DailyMessagingUpdateStatusService();
    }

    /**
     * Daily: get list
     * @example /api/messages/daily/sms
     * @example /api/messages/daily/email
     */
    public function index(DailyMessagesRequest $request): Responsable
    {
        $messages = $this->repository($request)->get();
        return DailyMessageCollection::make($messages);
    }

    /**
     * Daily: send
     * @example /api/messages/daily/sms/send
     * @example /api/messages/daily/email/send
     */
    public function send(DailyMessagesRequest $request): JsonResponse
    {
        $messages = $this->repository($request)->get();

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
                'job_id' => $result['response']['job_id'] ?? null,
            ],
            /**
             * @var array{messages: object[], channels: string[], channel_options: object}
             */
            'request' => [
                'messages' => $request['messages'],
                'channels' => $request['channels'],
            ],
        ]);
    }

    /**
     * Daily: get list as txt file
     * @example /api/messages/daily/sms/txt
     * @example /api/messages/daily/email/txt
     */
    public function txt(DailyMessagesRequest $request): Response|Application|ResponseFactory
    {
        $messages = $this->repository($request->type)->get();

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

    protected function repository(DailyMessagesRequest $request): DailyMessagesDataService
    {
       return DailyMessagesDataService::make()->setType($request->type);
    }

    protected function senderName(): string
    {
        return env('MTS_API_ALFA_NAME');
    }
}

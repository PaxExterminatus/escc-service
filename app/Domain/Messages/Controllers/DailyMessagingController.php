<?php

namespace App\Domain\Messages\Controllers;

use App\Domain\Messages\Models\MessagesDaily;
use App\Domain\Messages\Queries\DailyMessagesRepository;
use App\Domain\Messages\Services\MobileTeleSystems\MobileTeleSystemsProvider;
use App\Http\Controllers\ApiController;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class DailyMessagingController extends ApiController
{
    protected DailyMessagesRepository $dailyMessagesRepository;

    function __construct(DailyMessagesRepository $dailyMessagesRepository)
    {
        $this->dailyMessagesRepository = $dailyMessagesRepository;
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

        $result = MobileTeleSystemsProvider::make()->massSending($messages, $this->sendingName());

        $request = $result['request'];
        $response = $result['response'];

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

        $filename = $this->sendingName();

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-store, no-cache',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }

    protected function applyParamsToDailyMessagesRepository(string $type): DailyMessagesRepository
    {
        if (Str::upper($type) === MessagesDaily::$TYPE_SMS)
            $this->dailyMessagesRepository->setTypeAsSms();

        if (Str::upper($type) === MessagesDaily::$TYPE_EMAIL)
            $this->dailyMessagesRepository->setTypeAsEmail();

        return $this->dailyMessagesRepository;
    }

    protected function sendingName(): string
    {
        return env('MTS_API_ALFA_NAME');
    }
}

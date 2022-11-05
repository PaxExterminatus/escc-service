<?php

namespace App\Domain\Messages\Controllers;

use App\Domain\Messages\Models\MessagesDaily;
use App\Domain\Messages\Queries\DailyMessagesRepository;
use App\Domain\Messages\Services\MobileTeleSystems\Provider;
use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class DailyMessagingController extends ApiController
{
    protected DailyMessagesRepository $dailyMessagesRepository;

    function __construct(DailyMessagesRepository $dailyMessagesRepository)
    {
        $this->dailyMessagesRepository = $dailyMessagesRepository;
    }

    /** Get list of daily messages */
    function index(string $messageType): JsonResponse
    {
        $messages = $this->applyParamsToDailyMessagesRepository($messageType)->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /** Send daily messages */
    function send(string $messageType): JsonResponse
    {
        $messages = $this->applyParamsToDailyMessagesRepository($messageType)->get();

        $response = Provider::make()->massSending($messages);

        return response()->json([
            'response' => [
                'status' => $response->status(),
                'reason' => $response->reason(),
                'data' => $response->json(),
            ],
            'messages' => $messages,
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
}

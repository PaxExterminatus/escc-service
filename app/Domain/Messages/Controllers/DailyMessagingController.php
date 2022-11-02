<?php

namespace App\Domain\Messages\Controllers;


use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\Domain\Messages\Queries\DailyMessagesRepository;
use App\Domain\Messages\Queries\DailyMessagesQuery;
use App\Domain\Messages\Services\MobileTeleSystemsMessagingProvider;

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

        dd($messages);

        MobileTeleSystemsMessagingProvider::make()->massSending($messages);

        return response()->json([
            'messages' => $messages,
        ]);
    }

    protected function applyParamsToDailyMessagesRepository(string $type): DailyMessagesRepository
    {
        if (Str::upper($type) === 'SMS')
            $this->dailyMessagesRepository->setTypeAsSms();

        if (Str::upper($type) === DailyMessagesQuery::$TYPE_EMAIL)
            $this->dailyMessagesRepository->setTypeAsEmail();

        return $this->dailyMessagesRepository;
    }
}

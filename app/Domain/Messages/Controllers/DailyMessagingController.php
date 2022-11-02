<?php

namespace App\Domain\Messages\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\Domain\Messages\Queries\DailyMessagesQuery;
use App\Domain\Messages\Services\MessagingProviderByMobileTeleSystems;

class DailyMessagingController extends ApiController
{
    /** Get list of daily messages */
    function index(string $messageType): JsonResponse
    {
        $messages = $this->dailyMessagesQuery($messageType)->query()->get();

        MessagingProviderByMobileTeleSystems::make()->package($messages);

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /** Send daily messages */
    function send(string $messageType): JsonResponse
    {
        $messages = $this->dailyMessagesQuery($messageType)->query()->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    protected function dailyMessagesQuery(string $type): DailyMessagesQuery
    {
        $messages = DailyMessagesQuery::make();

        if (Str::upper($type) === DailyMessagesQuery::$TYPE_SMS)
            $messages->setTypeAsSms();

        if (Str::upper($type) === DailyMessagesQuery::$TYPE_EMAIL)
            $messages->setTypeAsEmail();

        return $messages;
    }
}

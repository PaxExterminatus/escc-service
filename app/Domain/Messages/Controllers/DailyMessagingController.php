<?php

namespace App\Domain\Messages\Controllers;

use App\Domain\Messages\Queries\DailyMessagesQuery;
use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class DailyMessagingController extends ApiController
{
    /** Send daily messages */
    function send(string $messageType): JsonResponse
    {
        $messages = $this->dailyMessages($messageType)->query()->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /** Get list of daily messages */
    function index(string $messageType): JsonResponse
    {
        $messages = $this->dailyMessages($messageType)->query()->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    protected function dailyMessages(string $type): DailyMessagesQuery
    {
        $messages = DailyMessagesQuery::make();

        if (Str::upper($type) === DailyMessagesQuery::$TYPE_SMS)
            $messages->setTypeAsSms();

        if (Str::upper($type) === DailyMessagesQuery::$TYPE_EMAIL)
            $messages->setTypeAsEmail();

        return $messages;
    }
}

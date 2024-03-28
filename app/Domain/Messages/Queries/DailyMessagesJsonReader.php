<?php

namespace App\Domain\Messages\Queries;

use App\Base\Repositories\JsonReader;
use App\Domain\Messages\Models\DailyMessageView;

class DailyMessagesJsonReader extends JsonReader
{
    use DailyMessagesParamsTrait;

    function get(): iterable
    {
        $messages = collect();

        foreach ($this->read('api.messages.daily.sms')['messages'] as $message)
        {
            $model = (new DailyMessageView())->fill($message);
            $messages->push($model);
        }

        return $messages;
    }
}

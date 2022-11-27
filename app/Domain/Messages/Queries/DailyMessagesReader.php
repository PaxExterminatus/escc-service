<?php

namespace App\Domain\Messages\Queries;

use App\Base\Repositories\JsonReader;
use App\Domain\Messages\Models\DailyMessage;

class DailyMessagesReader extends JsonReader implements DailyMessagesRepository
{
    use DailyMessagesParamsTrait;

    function get(): iterable
    {
        $messages = collect();

        foreach ($this->read('api.messages.daily.sms')['messages'] as $message)
        {
            $model = (new DailyMessage())->fill($message);
            $messages->push($model);
        }

        return $messages;
    }
}

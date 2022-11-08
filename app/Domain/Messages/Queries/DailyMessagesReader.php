<?php

namespace App\Domain\Messages\Queries;

use App\Base\Repositories\JsonReader;
use App\Domain\Messages\Models\MessagesDaily;

class DailyMessagesReader extends JsonReader implements DailyMessagesRepository
{
    use DailyMessagesParamsTrait;

    function get(): iterable
    {
        $messages = collect();

        foreach ($this->read('api.messages.daily.sms')['messages'] as $message)
        {
            $model = (new MessagesDaily())->fill($message);
            $messages->push($model);
        }

        return $messages;
    }
}

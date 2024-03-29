<?php

namespace App\Domain\Messages\Queries;

use App\Base\Repositories\JsonReader;
use App\Models\DailyMessage;
use Illuminate\Support\Collection;

class DailyMessagesSourceJson extends JsonReader
{
    use DailyMessagesParamsTrait;

    /**
     * @return Collection<DailyMessage>
     */
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

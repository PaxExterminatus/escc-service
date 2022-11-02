<?php

namespace App\Domain\Messages\Queries;

use App\Base\Repositories\JsonReader;

class DailyMessagesReader extends JsonReader implements DailyMessagesRepository
{
    use DailyMessagesParamsTrait;

    function get(): iterable
    {
        return $this->read('api.messages.daily.sms')['messages'];
    }
}

<?php

namespace App\Domain\Messages\DataService;

use App\Base\Repositories\JsonReader;
use App\Domain\Messages\Models\DailyMessage;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class DailyMessagesDataService
{
    static function make(): static
    {
        return new static;
    }

    protected string $type;

    public function setType(string $type): static
    {
        $this->type = Str::upper($type);
        return $this;
    }

    public function get(): Collection
    {
        if (USE_DEVELOPMENT_REPOSITORY_FOR_ALL || USE_DEVELOPMENT_REPOSITORY_FOR_DAILY_MESSAGES)
        {
            $messages = collect();

            foreach (JsonReader::make()->read('api.messages.daily.sms')['messages'] as $message)
            {
                $model = (new DailyMessage)->fill($message);
                $messages->push($model);
            }

            return $messages;
        }
        else
        {
            return DailyMessage::whereType($this->type)->get();
        }
    }
}

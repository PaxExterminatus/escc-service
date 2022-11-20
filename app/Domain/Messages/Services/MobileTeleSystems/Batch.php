<?php

namespace App\Domain\Messages\Services\MobileTeleSystems;

use Illuminate\Support\Collection;
use JsonSerializable;

class Batch implements JsonSerializable
{
    /** @var BatchMessage[]|Collection */
    protected iterable $messages;
    /** @var BatchChanel[]|Collection */
    protected iterable $channels;

    function __construct()
    {
        $this->messages = new Collection();
        $this->channels = new Collection();
    }

    static function make(): static
    {
        return new static;
    }

    function addMessage(BatchMessage $message): static
    {
        $this->messages->push($message);
        return $this;
    }

    function messages(): array
    {
        return $this->messages->map(function (BatchMessage $message) {
            return $message->toArray();
        })->toArray();
    }

    function addChannel(BatchChanel $chanel): static
    {
        $this->channels->push($chanel);
        return $this;
    }

    function toArray(): array
    {
        $data = [
            'messages' => $this->messages(),
            'channels' => [],
        ];

        foreach ($this->channels as $chanel)
        {
            $data['channels'][] = $chanel->getName();
            $data['channel_options'][$chanel->getName()] = $chanel->options();
        }

        return $data;
    }

    function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

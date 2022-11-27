<?php

namespace App\Domain\Messages\Services\MobileTeleSystems;

use Illuminate\Support\Collection;
use JsonSerializable;

class MobileTeleSystemsBatch implements JsonSerializable
{
    /** @var MobileTeleSystemsBatchMessage[]|Collection */
    protected iterable $messages;
    /** @var MobileTeleSystemsBatchChanel[]|Collection */
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

    function addMessage(MobileTeleSystemsBatchMessage $message): static
    {
        $this->messages->push($message);
        return $this;
    }

    function messages(): array
    {
        return $this->messages->map(function (MobileTeleSystemsBatchMessage $message) {
            return $message->toArray();
        })->toArray();
    }

    function addChannel(MobileTeleSystemsBatchChanel $chanel): static
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

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

    function addChannel(BatchChanel $chanel): static
    {
        $this->channels->push($chanel);
        return $this;
    }

    function toArray(): array
    {
        return [
            'messages' => $this->messages->map(function (BatchMessage $message) {
                return $message->toArray();
            })->toArray(),
            'channels' => $this->channels->map(function (BatchChanel $chanel) {
                return $chanel->getName();
            })->toArray(),
            'channel_options' => $this->channels->map(function (BatchChanel $chanel) {
                return $chanel->options();
            })->toArray(),
        ];
    }

    function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

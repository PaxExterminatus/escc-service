<?php

namespace App\Domain\Messages\Channels;

class MessageChannelRegistry
{
    /**
     * Новый канал (мессенджер) — новый класс, реализующий MessageChannel, и одна строка здесь.
     *
     * @var class-string<MessageChannel>[]
     */
    protected static array $channels = [
        SmsChannel::class,
        EmailChannel::class,
    ];

    /** @return MessageChannel[] */
    public static function all(): array
    {
        return array_map(fn (string $class) => new $class, static::$channels);
    }

    /** @return string[] */
    public static function codes(): array
    {
        return array_map(fn (MessageChannel $channel) => $channel->code(), static::all());
    }

    public static function find(string $code): MessageChannel
    {
        foreach (static::all() as $channel) {
            if ($channel->code() === $code) {
                return $channel;
            }
        }

        abort(422, "Unknown channel: {$code}");
    }
}

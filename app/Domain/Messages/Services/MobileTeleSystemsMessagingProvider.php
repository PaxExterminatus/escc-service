<?php

namespace App\Domain\Messages\Services;

use Illuminate\Support\Collection;
use App\Domain\Messages\Models\MessagesDaily;
use App\Domain\Messages\Services\MobileTeleSystems\Batch;
use App\Domain\Messages\Services\MobileTeleSystems\BatchMessage;

class MobileTeleSystemsMessagingProvider implements MessagingProviderInterface
{
    static function make(): static
    {
        return new static;
    }

    /** @param MessagesDaily[]|Collection $messages */
    function massSending(iterable $messages)
    {
        $batch = Batch::make();

        foreach ($messages as $message)
        {
            $batchMessage = BatchMessage::make()
                ->setPhoneNumber($message->address)
                ->setExtraId($message->id)
                ->setText($message->body);

            $batch->addMessage($batchMessage);
        }
    }
}

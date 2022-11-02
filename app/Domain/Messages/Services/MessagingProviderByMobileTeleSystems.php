<?php

namespace App\Domain\Messages\Services;

use App\Domain\Messages\Models\MessagesDaily;
use App\Domain\Messages\Services\MobileTeleSystems\BatchSending;
use App\Domain\Messages\Services\MobileTeleSystems\BatchSendingMessage;
use Illuminate\Support\Collection;

class MessagingProviderByMobileTeleSystems implements MessagingProviderPlan
{
    static function make(): static
    {
        return new static;
    }

    /** @param MessagesDaily[]|Collection $messages */
    function package(iterable $messages)
    {
        $batch = BatchSending::make();

        foreach ($messages as $message)
        {
            $batchMessage = BatchSendingMessage::make()
                ->setPhoneNumber($message->address)
                ->setExtraId($message->id)
                ->setText($message->body);

            $batch->addMessage($batchMessage);
        }
    }
}

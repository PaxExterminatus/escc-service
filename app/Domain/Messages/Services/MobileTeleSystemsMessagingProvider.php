<?php

namespace App\Domain\Messages\Services;

use App\Domain\Messages\Models\MessagesDaily;
use App\Domain\Messages\Services\MobileTeleSystems\BatchChanel;
use App\Domain\Messages\Services\MobileTeleSystems\Batch;
use App\Domain\Messages\Services\MobileTeleSystems\BatchMessage;

class MobileTeleSystemsMessagingProvider implements MessagingProviderInterface
{
    static function make(): static
    {
        return new static;
    }

    /** @param MessagesDaily[] $messages */
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

        $smsChanel = BatchChanel::make()
            ->setName('sms')
            ->setOptionAlphaName('TestAlphaName')
            ->setOptionTtl(300);

        $batch->addChannel($smsChanel);

        dd($batch->jsonSerialize());
    }
}

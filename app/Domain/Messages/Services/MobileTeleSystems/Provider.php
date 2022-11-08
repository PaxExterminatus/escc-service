<?php

namespace App\Domain\Messages\Services\MobileTeleSystems;

use App\Domain\Messages\Models\MessagesDaily;
use App\Domain\Messages\Services\MessagingProviderInterface;
use Illuminate\Http\Client\Response;
use GuzzleHttp\Promise\PromiseInterface;

class Provider implements MessagingProviderInterface
{
    protected ProviderAPI $api;

    function __construct()
    {
        $this->api = new ProviderAPI;
    }

    static function make(): static
    {
        return new static;
    }

    /** @param MessagesDaily[] $messages */
    function massSending(iterable $messages): PromiseInterface|Response
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

        return $this->api->batch($batch->toArray());
    }
}

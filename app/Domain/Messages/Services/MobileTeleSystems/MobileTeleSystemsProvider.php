<?php

namespace App\Domain\Messages\Services\MobileTeleSystems;

use App\Domain\Messages\Models\MessagesDaily;
use App\Domain\Messages\Services\MessagingProviderInterface;
use Illuminate\Http\Client\Response;
use GuzzleHttp\Promise\PromiseInterface;

class MobileTeleSystemsProvider implements MessagingProviderInterface
{
    protected MobileTeleSystemsApi $api;

    function __construct()
    {
        $this->api = new MobileTeleSystemsApi;
    }

    static function make(): static
    {
        return new static;
    }

    /**
     * @param MessagesDaily[] $messages
     * @return array{response: PromiseInterface|Response, request: array}
     */
    function massSending(iterable $messages, string $name): array
    {
        $batch = MobileTeleSystemsBatch::make();

        foreach ($messages as $message)
        {
            $batchMessage = MobileTeleSystemsBatchMessage::make()
                ->setPhoneNumber($message->address)
                ->setExtraId($message->id)
                ->setText($message->body);

            $batch->addMessage($batchMessage);
        }

        $smsChanel = MobileTeleSystemsBatchChanel::make()
            ->setName('sms')
            ->setOptionAlphaName($name)
            ->setOptionTtl(300);

        $batch->addChannel($smsChanel);

        $request = $batch->toArray();
        $response = $this->api->batch($request);

        return [
            'response' => $response,
            'request' => $request,
        ];
    }
}

<?php

namespace App\Domain\Messages\Services\MobileTeleSystems;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class MobileTeleSystemsApi
{
    protected string $clientId;
    protected PendingRequest $http;

    function __construct()
    {
        $this->clientId = env('MTS_API_CLIENT_ID');
        $this->http = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
            ->withBasicAuth(env('MTS_API_LOGIN'), env('MTS_API_PASSWORD'))
            ->withOptions(['verify' => false]);
    }

    protected function base(): string
    {
        return "https://api.communicator.mts.by/$this->clientId/json2/";
    }

    function batch(array $data): PromiseInterface|Response
    {
        return $this->http->post($this->base() . 'batch', $data);
    }
}

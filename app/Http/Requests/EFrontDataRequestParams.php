<?php


namespace App\Http\Requests;


class EFrontDataRequestParams
{
    public ?string $clientCode;
    public ?string $requestType;
    public ?string $requestCode;
    public ?string $nodeId;
    public ?string $lessonItem;

    public function __construct(array $requestAll)
    {
        $this->clientCode = $requestAll['cl_code'] ?? null;
        $this->requestType = $requestAll['type'] ?? null;
        $this->requestCode = $requestAll['code'] ?? null;
        $this->nodeId = $requestAll['node_id'] ?? null;
        $this->lessonItem = $requestAll['lesson_item'] ?? null;
    }
}

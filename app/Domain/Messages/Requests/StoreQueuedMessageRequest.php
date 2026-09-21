<?php

namespace App\Domain\Messages\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;
use App\Domain\Messages\Enums\MessageTypeEnum;
use Illuminate\Validation\Rule;

/**
 * @property string type
 * @property string address
 * @property string body
 */
class StoreQueuedMessageRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_column(MessageTypeEnum::cases(), 'name'))],
            'address' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }
}

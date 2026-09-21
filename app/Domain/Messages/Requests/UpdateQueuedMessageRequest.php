<?php

namespace App\Domain\Messages\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;

/**
 * @property string address
 * @property string body
 */
class UpdateQueuedMessageRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }
}

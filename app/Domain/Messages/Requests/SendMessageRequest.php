<?php

namespace App\Domain\Messages\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;
use App\Domain\Messages\Channels\MessageChannelRegistry;
use Illuminate\Validation\Rule;

/**
 * @property int client_id
 * @property string channel
 * @property int|null template_id
 * @property string|null body
 * @property array params
 */
class SendMessageRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            'client_id' => 'required|integer',
            'channel' => ['required', Rule::in(MessageChannelRegistry::codes())],
            'template_id' => 'nullable|integer',
            'body' => 'nullable|string|required_without:template_id',
            'params' => 'array',
        ];
    }
}

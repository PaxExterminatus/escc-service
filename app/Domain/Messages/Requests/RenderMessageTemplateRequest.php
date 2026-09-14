<?php

namespace App\Domain\Messages\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;

/**
 * @property int client_id
 */
class RenderMessageTemplateRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            'client_id' => 'required|integer',
        ];
    }
}

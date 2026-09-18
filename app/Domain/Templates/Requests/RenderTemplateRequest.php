<?php

namespace App\Domain\Templates\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;

/**
 * @property int client_id
 */
class RenderTemplateRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            'client_id' => 'required|integer',
        ];
    }
}

<?php

namespace App\Domain\Templates\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;

/**
 * @property int client_id
 * @property string body
 * @property int|null template_id
 */
class EmailPreviewRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            'client_id' => 'required|integer',
            'body' => 'nullable|string',
            'template_id' => 'nullable|integer',
        ];
    }
}

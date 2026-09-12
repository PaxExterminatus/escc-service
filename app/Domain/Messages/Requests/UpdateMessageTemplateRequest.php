<?php

namespace App\Domain\Messages\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;

/**
 * @property string code
 * @property string name
 * @property string body
 * @property bool is_active
 */
class UpdateMessageTemplateRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'code' => "required|string|max:64|unique:OPERATOR_MESSAGE_TEMPLATE,CODE,{$id},TEMPLATE_ID",
            'name' => 'required|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ];
    }
}

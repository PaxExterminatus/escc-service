<?php

namespace App\Domain\Messages\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;
use App\Domain\Messages\Enums\MessageTypeEnum;
use Illuminate\Validation\Rule;

/**
 * @property string type
 */
class DailyMessagesRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            // Route segment is the type's case name ('sms'/'email'), not its numeric EMSG_TYPE value.
            'type' => ['required', Rule::in(array_column(MessageTypeEnum::cases(), 'name'))],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Type is required',
            'type.in' => 'Valid values is ' . implode(', ', array_column(MessageTypeEnum::cases(), 'name')),
        ];
    }
}

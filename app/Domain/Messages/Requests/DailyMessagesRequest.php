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
            // Message sending channels
            'type' => ['required', Rule::enum(MessageTypeEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Type is required',
            'type.Illuminate\Validation\Rules\Enum' => 'Valid values is ' . MessageTypeEnum::sms->value . ', ' . MessageTypeEnum::email->value,
        ];
    }
}

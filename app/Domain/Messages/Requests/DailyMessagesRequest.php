<?php

namespace App\Domain\Messages\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;
use App\Domain\Messages\Enums\MessageTypeEnum;
use Illuminate\Validation\Rule;

/**
 * @property string type
 * @property string|null from
 * @property string|null to
 */
class DailyMessagesRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            // Route segment is the type's case name ('sms'/'email'), not its numeric EMSG_TYPE value.
            'type' => ['required', Rule::in(array_column(MessageTypeEnum::cases(), 'name'))],
            // Оба необязательны и независимы: ни одного — без ограничения по дате ("все неотправленные").
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
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

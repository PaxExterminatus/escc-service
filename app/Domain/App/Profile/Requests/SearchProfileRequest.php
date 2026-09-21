<?php

namespace App\Domain\App\Profile\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;
use Illuminate\Validation\Rule;

/**
 * @property string|null client_code
 * @property string|null name
 * @property string|null phone
 * @property string|null email
 * @property string|null birthday_from
 * @property string|null birthday_to
 * @property string|null sex
 * @property int|null page
 */
class SearchProfileRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            'client_code' => ['nullable', 'string', 'max:64'],
            'name' => ['nullable', 'string', 'max:128'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'string', 'max:128'],
            'birthday_from' => ['nullable', 'date_format:Y-m-d'],
            'birthday_to' => ['nullable', 'date_format:Y-m-d'],
            'sex' => ['nullable', Rule::in(['man', 'woman'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

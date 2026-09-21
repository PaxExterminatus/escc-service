<?php

namespace App\Domain\App\Course\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;

/**
 * @property string|null sub_code
 * @property int|null client_id
 * @property string|null client_name
 * @property string|null course_name
 * @property int|null status_id
 * @property string|null start_date_from
 * @property string|null start_date_to
 * @property int|null page
 */
class SearchCourseRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            'sub_code' => ['nullable', 'string', 'max:64'],
            'client_id' => ['nullable', 'integer'],
            'client_name' => ['nullable', 'string', 'max:128'],
            'course_name' => ['nullable', 'string', 'max:128'],
            'status_id' => ['nullable', 'integer'],
            'start_date_from' => ['nullable', 'date_format:Y-m-d'],
            'start_date_to' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

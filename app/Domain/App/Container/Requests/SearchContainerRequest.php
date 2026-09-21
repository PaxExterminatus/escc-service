<?php

namespace App\Domain\App\Container\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;
use App\Domain\App\Container\Enums\ContainerStatusEnum;
use Illuminate\Validation\Rule;

/**
 * @property string|null container_code
 * @property int|null client_id
 * @property string|null client_code
 * @property string|null client_name
 * @property int|null status_id
 * @property string|null send_date_from
 * @property string|null send_date_to
 * @property int|null page
 */
class SearchContainerRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            'container_code' => ['nullable', 'string', 'max:64'],
            'client_id' => ['nullable', 'integer'],
            'client_code' => ['nullable', 'string', 'max:64'],
            'client_name' => ['nullable', 'string', 'max:128'],
            'status_id' => ['nullable', Rule::in(array_column(ContainerStatusEnum::cases(), 'value'))],
            'send_date_from' => ['nullable', 'date_format:Y-m-d'],
            'send_date_to' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

<?php

namespace App\Domain\Scheduler\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;

/**
 * @property string run_time
 * @property bool is_enabled
 */
class UpdateScheduledTaskRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            'run_time' => ['required', 'date_format:H:i'],
            'is_enabled' => ['required', 'boolean'],
        ];
    }
}

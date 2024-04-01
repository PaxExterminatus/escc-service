<?php

namespace App\Http\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;
use App\Domain\EFront\Enums\EFrontRequestTypeEnum;
use Illuminate\Validation\Rule;

/**
 * Class EFrontDataRequest
 * @package App\Http\Requests
 *
 * @property int cl_code
 * @property string rq_type
 * @property string rq_code
 * @property int node_id
 * @property int lesson_item
 */
class EFrontDataRequest extends ApplicationProgrammingInterfaceRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Aurora client ID
            'cl_code' => ['required','int'],
            // Request type
            'rq_type' => ['required', Rule::enum(EFrontRequestTypeEnum::class)],
            // Request code
            'rq_code' => [],
            // Aurora node ID
            'node_id' => ['int'],
            // Aurora lesson item id
            'lesson_item' => ['int'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;
use App\Domain\EFront\Enums\EFrontRequestTypeEnum;
use Illuminate\Validation\Rule;

/**
 * @property int cl_code
 * @property string type
 * @property string code
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
            'type' => ['required', Rule::enum(EFrontRequestTypeEnum::class)],
            // Request PIN code
            'code' => [],
            // Aurora node ID
            'node_id' => ['int'],
            // Aurora lesson item id
            'lesson_item' => ['int'],
        ];
    }
}

<?php

namespace App\Http\Requests;

/**
 * Class EFrontDataRequest
 * @package App\Http\Requests
 * @method EFrontDataRequestParams params()
 */
class EFrontDataRequest extends BaseFormRequest
{
    protected string $paramsClass = EFrontDataRequestParams::class;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}

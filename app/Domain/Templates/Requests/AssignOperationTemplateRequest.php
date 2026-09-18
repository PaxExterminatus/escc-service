<?php

namespace App\Domain\Templates\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;
use App\Domain\Templates\Enums\TemplateOperationEnum;
use App\Domain\Templates\Enums\TemplateTypeEnum;
use Illuminate\Validation\Rule;

/**
 * Назначение шаблона операции: docx — файлом, html — текстом редактора.
 *
 * @property int operation_id
 * @property int type_id
 * @property string name
 * @property \Illuminate\Http\UploadedFile|null file
 * @property string|null body
 * @property int|null wrapper_id только для html — какую обёртку письма использовать
 * @property bool|null wrapper_auto только для html — выбрать обёртку по балансу клиента
 */
class AssignOperationTemplateRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            'operation_id' => ['required', Rule::in(array_column(TemplateOperationEnum::cases(), 'value'))],
            'type_id' => ['required', Rule::in([TemplateTypeEnum::docx->value, TemplateTypeEnum::html->value])],
            'name' => 'required|string|max:255',
            'file' => 'required_if:type_id,' . TemplateTypeEnum::docx->value . '|file|mimes:docx',
            'body' => 'required_if:type_id,' . TemplateTypeEnum::html->value . '|string',
            'wrapper_id' => ['nullable', 'integer', Rule::exists('SERVICE_TEMPLATE', 'TEMPLATE_ID')->where('TYPE_ID', TemplateTypeEnum::wrapper->value)],
            'wrapper_auto' => 'boolean',
        ];
    }
}

<?php

namespace App\Domain\Templates\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;
use App\Domain\Templates\Enums\TemplateTypeEnum;
use App\Domain\Templates\Enums\TemplateWrapperRoleEnum;
use App\Rules\ContainsBodyTagRule;
use Illuminate\Validation\Rule;

/**
 * @property string code
 * @property string name
 * @property string body
 * @property bool is_active
 * @property int|null type_id свободный шаблон (text) по умолчанию, либо явно wrapper
 * @property int|null wrapper_id только для text/html — какую обёртку письма использовать
 * @property bool|null wrapper_auto только для text/html — выбрать обёртку по балансу клиента
 * @property bool|null is_default только для wrapper — обёртка по умолчанию
 * @property int|null wrapper_role только для wrapper — роль в авто-выборе по балансу
 */
class StoreTemplateRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:64|unique:SERVICE_TEMPLATE,CODE',
            'name' => 'required|string|max:255',
            'body' => ['required', 'string', Rule::when(
                $this->integer('type_id') === TemplateTypeEnum::wrapper->value,
                [new ContainsBodyTagRule()]
            )],
            'is_active' => 'boolean',
            'type_id' => ['nullable', Rule::in([TemplateTypeEnum::text->value, TemplateTypeEnum::wrapper->value])],
            'wrapper_id' => ['nullable', 'integer', Rule::exists('SERVICE_TEMPLATE', 'TEMPLATE_ID')->where('TYPE_ID', TemplateTypeEnum::wrapper->value)],
            'wrapper_auto' => 'boolean',
            'is_default' => 'boolean',
            'wrapper_role' => ['nullable', Rule::in(array_column(TemplateWrapperRoleEnum::cases(), 'value'))],
        ];
    }
}

<?php

namespace App\Domain\Templates\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;
use App\Domain\Templates\Enums\TemplateTypeEnum;
use App\Domain\Templates\Enums\TemplateWrapperRoleEnum;
use App\Domain\Templates\Models\Template;
use App\Rules\ContainsBodyTagRule;
use Illuminate\Validation\Rule;

/**
 * Тип шаблона (text/wrapper) неизменен после создания — здесь не запрашивается.
 *
 * @property string code
 * @property string name
 * @property string body
 * @property bool is_active
 * @property int|null wrapper_id только для text/html — какую обёртку письма использовать
 * @property bool|null wrapper_auto только для text/html — выбрать обёртку по балансу клиента
 * @property bool|null is_default только для wrapper — обёртка по умолчанию
 * @property int|null wrapper_role только для wrapper — роль в авто-выборе по балансу
 */
class UpdateTemplateRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'code' => "required|string|max:64|unique:SERVICE_TEMPLATE,CODE,{$id},TEMPLATE_ID",
            'name' => 'required|string|max:255',
            'body' => ['required', 'string', Rule::when($this->editsWrapper(), [new ContainsBodyTagRule()])],
            'is_active' => 'boolean',
            'wrapper_id' => ['nullable', 'integer', Rule::exists('SERVICE_TEMPLATE', 'TEMPLATE_ID')->where('TYPE_ID', TemplateTypeEnum::wrapper->value)],
            'wrapper_auto' => 'boolean',
            'is_default' => 'boolean',
            'wrapper_role' => ['nullable', Rule::in(array_column(TemplateWrapperRoleEnum::cases(), 'value'))],
        ];
    }

    /**
     * Тип шаблона при обновлении не передаётся (он неизменен) — берём его у самой записи,
     * чтобы понять, требовать ли спецтег {BODY}.
     */
    protected function editsWrapper(): bool
    {
        // Template::casts() приводит TYPE_ID к TemplateTypeEnum ещё на стороне модели —
        // value() отдаёт уже готовый enum, а не число, кастовать через (int) незачем.
        return Template::where('template_id', $this->route('id'))->value('type_id') === TemplateTypeEnum::wrapper;
    }
}

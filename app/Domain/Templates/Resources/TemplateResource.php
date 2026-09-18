<?php

namespace App\Domain\Templates\Resources;

use App\Domain\Templates\Enums\TemplateTypeEnum;
use App\Domain\Templates\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Единственное представление шаблона в API — и для библиотеки шаблонов, и для шаблонов,
 * назначенных операциям (раньше у последних был свой урезанный формат без id и типа).
 *
 * @mixin Template
 */
class TemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->template_id,
            'code' => $this->code,
            'name' => $this->name,
            'body' => $this->body,
            'is_active' => (bool) $this->is_active,
            'type_id' => $this->type_id->value,
            'operation_id' => $this->operation_id !== null ? (int) $this->operation_id : null,
            'wrapper_id' => $this->wrapper_id !== null ? (int) $this->wrapper_id : null,
            'wrapper_auto' => (bool) $this->wrapper_auto,
            'wrapper_role' => $this->wrapper_role?->value,
            'is_default' => (bool) $this->is_default,
            // Какая обёртка реально применится с учётом фолбэков (см. EmailComposer::wrap) —
            // чтобы интерфейс не повторял это правило у себя. При wrapper_auto заранее не
            // известно (зависит от баланса в момент отправки), поэтому здесь null. У docx и у
            // самих обёрток понятие "своя обёртка" не имеет смысла — документ не оборачивается
            // в письмо, поэтому здесь тоже null (а не название обёртки по умолчанию "на всякий
            // случай", как было бы, если бы этот метод считал её для любого типа).
            'effective_wrapper_name' => ($this->supportsWrapper() && !$this->wrapper_auto) ? $this->effectiveWrapperName() : null,
            'uploaded_at' => $this->uploaded_at?->toIso8601String(),
        ];
    }

    protected function supportsWrapper(): bool
    {
        return in_array($this->type_id, [TemplateTypeEnum::text, TemplateTypeEnum::html], true);
    }

    protected function effectiveWrapperName(): ?string
    {
        $wrapper = ($this->wrapper_id ? Template::activeWrapper($this->wrapper_id) : null) ?? Template::defaultWrapper();

        return $wrapper?->name;
    }
}

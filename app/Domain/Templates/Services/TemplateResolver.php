<?php

namespace App\Domain\Templates\Services;

use App\Domain\Templates\Enums\TemplateOperationEnum;
use App\Domain\Templates\Enums\TemplateTypeEnum;
use App\Domain\Templates\Models\Template;
use Illuminate\Support\Facades\Storage;

/**
 * Разрешает путь к docx-шаблону операции: если оператор загрузил свой файл — используется он,
 * иначе — встроенный шаблон по умолчанию (resources/templates), если для операции такой есть.
 */
class TemplateResolver
{
    public function docxPathFor(TemplateOperationEnum $operation): string
    {
        $template = Template::forOperation($operation, TemplateTypeEnum::docx);

        if ($template) {
            return Storage::path($template->filename);
        }

        return $this->defaultDocxPathFor($operation);
    }

    protected function defaultDocxPathFor(TemplateOperationEnum $operation): string
    {
        return match ($operation) {
            TemplateOperationEnum::invoice => resource_path('templates/invoice.docx'),
            default => abort(404, "Нет шаблона документа для операции {$operation->label()}."),
        };
    }
}

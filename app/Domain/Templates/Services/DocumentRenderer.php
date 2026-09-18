<?php

namespace App\Domain\Templates\Services;

use App\Domain\Templates\Enums\TemplateOperationEnum;
use PhpOffice\PhpWord\TemplateProcessor;

/**
 * Универсальный рендер docx-шаблона операции: клонирует блок ${operation->blockName()}
 * по числу строк и заполняет каждый клон уже посчитанными тегами (см. TagResolver) — ничего
 * не знает про контейнеры/клиентов/конкретную операцию, только про теги и шаблон.
 */
class DocumentRenderer
{
    public function __construct(protected TemplateResolver $templates)
    {
    }

    /**
     * @param TemplateOperationEnum $operation
     * @param array<int, array<string, string>> $rows каждая строка — набор тег => значение (из TagResolver)
     * @return string путь к сгенерированному .docx во временной директории
     */
    public function render(TemplateOperationEnum $operation, array $rows): string
    {
        $processor = new TemplateProcessor($this->templates->docxPathFor($operation));
        $processor->cloneBlock($operation->blockName(), count($rows), true, true);

        foreach (array_values($rows) as $i => $row) {
            $index = $i + 1;

            foreach ($row as $tag => $value) {
                $processor->setValue("{$tag}#{$index}", $value);
            }
        }

        // tempnam() сразу создаёт файл, но PHPWord пишет по пути с расширением — исходную
        // пустышку нужно убрать руками, иначе она остаётся в temp навсегда (вызывающий код
        // удаляет только .docx). За день печати счетов такие нули копятся сотнями.
        $reserved = tempnam(sys_get_temp_dir(), 'document_');
        $path = $reserved . '.docx';
        @unlink($reserved);

        $processor->saveAs($path);

        return $path;
    }
}

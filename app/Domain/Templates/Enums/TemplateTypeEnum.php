<?php

namespace App\Domain\Templates\Enums;

/**
 * Носитель шаблона. Определяет, что хранится в SERVICE_TEMPLATE.BODY/FILENAME и как шаблон
 * заполняется — простой str_replace('{tag}', ...) для text/html, PHPWord TemplateProcessor
 * для docx (см. Template::render() и App\Domain\Templates\Services\TemplateResolver).
 */
enum TemplateTypeEnum: int
{
    case text = 1;
    case html = 2;
    case docx = 3;
    case wrapper = 4;

    public function label(): string
    {
        return match ($this) {
            self::text => 'Текст (SMS/Email)',
            self::html => 'HTML (письмо)',
            self::docx => 'Word (Документ)',
            self::wrapper => 'Обёртка письма (HTML)',
        };
    }
}

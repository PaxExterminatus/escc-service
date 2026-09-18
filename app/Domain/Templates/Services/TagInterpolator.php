<?php

namespace App\Domain\Templates\Services;

/**
 * Подстановка значений тегов вместо {плейсхолдеров}.
 *
 * Тривиальная операция, но раньше была скопирована в трёх местах (Template::render,
 * EmailComposer, TemplateController) — из-за чего любое уточнение правил (например,
 * экранирование значений для html) пришлось бы вносить трижды.
 */
class TagInterpolator
{
    /**
     * @param array<string, string> $params тег => значение (см. TagResolver)
     * @param bool $escapeHtml экранировать значения — когда результат попадает в html-разметку
     *                         (письмо), чтобы ФИО с < или & не ломали вёрстку
     */
    public static function apply(string $subject, array $params, bool $escapeHtml = false): string
    {
        foreach ($params as $key => $value) {
            $value = (string) $value;

            $subject = str_replace('{' . $key . '}', $escapeHtml ? e($value) : $value, $subject);
        }

        return $subject;
    }
}

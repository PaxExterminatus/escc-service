<?php

namespace App\Domain\Templates\Enums;

/**
 * Операции, для которых можно назначить шаблон(ы) — см. TemplateController. У одной операции
 * может быть несколько назначенных шаблонов одновременно, по одному на каждый TemplateTypeEnum
 * (см. UNIQUE(OPERATION_ID, TYPE_ID) в миграции SERVICE_TEMPLATE) — например, у "Печать счёта" может
 * быть свой docx (для печати/вложения) и свой html (для тела письма) одновременно.
 *
 * Обёртка письма (шапка+футер) больше не операция — это свободная библиотека шаблонов типа
 * TemplateTypeEnum::wrapper (см. Template::wrappers()), из которой каждый html-шаблон выбирает
 * себе одну через WRAPPER_ID (или использует помеченную IS_DEFAULT, если не выбрал).
 */
enum TemplateOperationEnum: int
{
    case invoice = 1;

    /**
     * У "Печать счёта" смысл операции разный в зависимости от носителя — docx печатается/
     * прикладывается к письму, а html и есть само письмо, поэтому у него отдельная подпись.
     */
    public function label(?TemplateTypeEnum $type = null): string
    {
        return $type === TemplateTypeEnum::html ? 'Счёт по email' : 'Печать счёта';
    }

    /**
     * Название операции безотносительно носителя — когда носители нужно показать как один
     * документ (см. TemplateController::operations(), группировка в интерфейсе), а не как
     * разные назначения.
     */
    public function groupLabel(): string
    {
        return match ($this) {
            self::invoice => 'Счёт',
        };
    }

    /**
     * Есть ли встроенный шаблон по умолчанию, которым система пользуется, пока оператор не
     * назначил свой — см. TemplateResolver::defaultDocxPathFor. У html-содержимого счёта
     * такого фолбэка нет — без назначенного шаблона отправить письмо нельзя (см. EmailComposer).
     */
    public function hasBuiltInDefault(TemplateTypeEnum $type): bool
    {
        return $type === TemplateTypeEnum::docx;
    }

    /**
     * Имя ${...}...${/...} блока в docx-шаблоне операции (см. DocumentRenderer::render) —
     * имеет смысл только для docx-носителя.
     */
    public function blockName(): string
    {
        return 'invoices';
    }

    /**
     * Какие носители (TemplateTypeEnum) применимы для этой операции.
     *
     * @return TemplateTypeEnum[]
     */
    public function applicableTypes(): array
    {
        return [TemplateTypeEnum::docx, TemplateTypeEnum::html];
    }
}

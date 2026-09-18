<?php

namespace App\Domain\Templates\Enums;

/**
 * Категория тега — для группировки в UI и в списке для внешнего Word-расширения. Не путать
 * с TagScopeEnum: категория — это "про что тег" (человеку), scope — "где он разрешён" (коду).
 * Например amount — client-scope (считается по клиенту), но по смыслу это "Финансы".
 */
enum TagCategoryEnum: int
{
    case client = 1;
    case finance = 2;
    case course = 3;
    case shipment = 4;

    public function label(): string
    {
        return match ($this) {
            self::client => 'Клиент',
            self::finance => 'Финансы',
            self::course => 'Курс',
            self::shipment => 'Посылка',
        };
    }
}

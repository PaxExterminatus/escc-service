<?php

namespace App\Domain\Templates\Enums;

/**
 * Где тег может быть разрешён (см. TagResolver): client — доступен и в сообщениях, и в
 * документах (привязан только к клиенту); invoice — только в документах по счёту, где
 * есть конкретный контейнер (client-теги в этом контексте тоже доступны, см.
 * TagResolver::resolveForContainer).
 */
enum TagScopeEnum: int
{
    case client = 1;
    case invoice = 2;

    public function label(): string
    {
        return match ($this) {
            self::client => 'Клиент',
            self::invoice => 'Счёт (контейнер)',
        };
    }
}

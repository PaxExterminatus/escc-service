<?php

namespace App\Domain\Templates\Enums;

/**
 * Роль обёртки письма в авто-выборе по балансу клиента (тег {amount}) — см.
 * EmailComposer::wrapperForBalance. Обёртка без роли (например нейтральная "по умолчанию")
 * в авто-выборе не участвует, её можно назначать только явно через WRAPPER_ID.
 */
enum TemplateWrapperRoleEnum: int
{
    case debt = 1;
    case positive = 2;

    public function label(): string
    {
        return match ($this) {
            self::debt => 'Есть задолженность',
            self::positive => 'Задолженности нет',
        };
    }
}

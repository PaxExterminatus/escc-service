<?php

namespace App\Domain\Templates;

use App\Domain\Templates\Enums\TagCategoryEnum;
use App\Domain\Templates\Enums\TagScopeEnum;

/**
 * Описание одного тега-плейсхолдера: код (то, что подставляют в шаблон), человекочитаемое
 * описание (для UI и для API — им пользуется в т.ч. внешнее Word-расширение), категория
 * (группировка в UI, см. TagCategoryEnum) и область действия (см. TagScopeEnum).
 */
final class TagDefinition
{
    public function __construct(
        public readonly string $code,
        public readonly string $description,
        public readonly TagCategoryEnum $category,
        public readonly TagScopeEnum $scope,
    ) {
    }
}

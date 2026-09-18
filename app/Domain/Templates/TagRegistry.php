<?php

namespace App\Domain\Templates;

use App\Domain\Templates\Enums\TagCategoryEnum;
use App\Domain\Templates\Enums\TagScopeEnum;

/**
 * Единый список тегов-плейсхолдеров, доступных при составлении шаблонов сообщений (SMS/Email)
 * и документов (счёт). Добавление нового тега — новая запись здесь и его вычисление
 * в TagResolver; дальше он автоматически появляется и в API (/api/tags — им пользуется
 * в т.ч. будущее Word-расширение), и в подсказках на страницах шаблонов.
 */
class TagRegistry
{
    /**
     * @return TagDefinition[]
     */
    public static function all(): array
    {
        return [
            new TagDefinition('client_code', 'Код клиента', TagCategoryEnum::client, TagScopeEnum::client),
            new TagDefinition('client_name', 'ФИО клиента', TagCategoryEnum::client, TagScopeEnum::client),
            new TagDefinition('client_phone', 'Телефон клиента', TagCategoryEnum::client, TagScopeEnum::client),
            new TagDefinition('client_email', 'Email клиента', TagCategoryEnum::client, TagScopeEnum::client),
            new TagDefinition('client_birthday', 'Дата рождения клиента', TagCategoryEnum::client, TagScopeEnum::client),

            new TagDefinition('amount', 'Текущая задолженность клиента, руб.', TagCategoryEnum::finance, TagScopeEnum::client),
            new TagDefinition('payment_last_date', 'Дата последнего платежа по посылке', TagCategoryEnum::finance, TagScopeEnum::invoice),
            new TagDefinition('invoice_number', 'Номер счёта (ID контейнера)', TagCategoryEnum::finance, TagScopeEnum::invoice),
            new TagDefinition('invoice_date', 'Дата выставления счёта (дата отправки)', TagCategoryEnum::finance, TagScopeEnum::invoice),
            new TagDefinition('item_name', 'Наименование позиции в счёте', TagCategoryEnum::finance, TagScopeEnum::invoice),
            new TagDefinition('item_amount', 'Сумма позиции, руб.', TagCategoryEnum::finance, TagScopeEnum::invoice),
            new TagDefinition('total_amount', 'Итоговая сумма к оплате по счёту, руб.', TagCategoryEnum::finance, TagScopeEnum::invoice),

            new TagDefinition('course_name', 'Название курса по подписке посылки', TagCategoryEnum::course, TagScopeEnum::invoice),
            new TagDefinition('next_send_date', 'Дата следующей отправки урока по подписке', TagCategoryEnum::course, TagScopeEnum::invoice),

            new TagDefinition('container_code', 'Код посылки (контейнера)', TagCategoryEnum::shipment, TagScopeEnum::invoice),
        ];
    }
}

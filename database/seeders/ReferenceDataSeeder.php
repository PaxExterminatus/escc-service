<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\GuardsAgainstNonTestDatabase;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Справочники и стабы, нужные для FK-целостности при заполнении тестовых данных
 * (см. TestClientDataSeeder). Значения id/названий взяты из пакета констант REV_CONST
 * там, где это было возможно — источник указан в комментарии к каждой строке.
 * Где в REV_CONST константы не нашлось (CATEGORY_TYPE, MONEY_SOURCE_TYPE,
 * EMSG_STATUS/EMSG_TYPE) — значение придумано для теста, это отмечено явно,
 * НЕ выдавайте эти конкретные id/имена за официальные без проверки на реальной БД.
 *
 * Идемпотентно: перед каждой вставкой проверяется exists(), поэтому безопасно
 * гонять повторно на одной и той же БД.
 *
 * Только для локальной тестовой XE — см. GuardsAgainstNonTestDatabase::guardTestDatabaseOnly().
 *
 * php artisan db:seed --class="Database\Seeders\ReferenceDataSeeder"
 */
class ReferenceDataSeeder extends Seeder
{
    use GuardsAgainstNonTestDatabase;

    protected string $connection = 'oracle';

    public function run(): void
    {
        $this->guardTestDatabaseOnly($this->connection);
        $db = DB::connection($this->connection);

        // CLIENT_TYPE — REV_CONST.clientType*
        $this->ensure($db, 'CLIENT_TYPE', 'TYPE_ID', 1, ['TYPE_NAME' => 'Customer']);
        $this->ensure($db, 'CLIENT_TYPE', 'TYPE_ID', 2, ['TYPE_NAME' => 'Company']);
        $this->ensure($db, 'CLIENT_TYPE', 'TYPE_ID', 3, ['TYPE_NAME' => 'Unknown']);

        // CLIENT_STATUS — REV_CONST.clientStatus*
        $this->ensure($db, 'CLIENT_STATUS', 'STATUS_ID', 1, ['STATUS_NAME' => 'Active']);
        $this->ensure($db, 'CLIENT_STATUS', 'STATUS_ID', 2, ['STATUS_NAME' => 'Not Active']);

        // CLIENT_SUB_STATUS — REV_CONST.subStatus*
        $this->ensure($db, 'CLIENT_SUB_STATUS', 'STATUS_ID', 1, ['STATUS_NAME' => 'Active']);
        $this->ensure($db, 'CLIENT_SUB_STATUS', 'STATUS_ID', 2, ['STATUS_NAME' => 'Not Active']);
        $this->ensure($db, 'CLIENT_SUB_STATUS', 'STATUS_ID', 3, ['STATUS_NAME' => 'Error']);
        $this->ensure($db, 'CLIENT_SUB_STATUS', 'STATUS_ID', 4, ['STATUS_NAME' => 'Finished']);
        $this->ensure($db, 'CLIENT_SUB_STATUS', 'STATUS_ID', 7, ['STATUS_NAME' => 'Refusing']);

        // CONTAINER_STATUS — REV_CONST.boxStatus*
        $this->ensure($db, 'CONTAINER_STATUS', 'STATUS_ID', -1, ['STATUS_NAME' => 'Temporary']);
        $this->ensure($db, 'CONTAINER_STATUS', 'STATUS_ID', 1, ['STATUS_NAME' => 'Stopped']);
        $this->ensure($db, 'CONTAINER_STATUS', 'STATUS_ID', 2, ['STATUS_NAME' => 'In Progress']);
        $this->ensure($db, 'CONTAINER_STATUS', 'STATUS_ID', 3, ['STATUS_NAME' => 'Error']);
        $this->ensure($db, 'CONTAINER_STATUS', 'STATUS_ID', 4, ['STATUS_NAME' => 'Canceled']);
        $this->ensure($db, 'CONTAINER_STATUS', 'STATUS_ID', 45, ['STATUS_NAME' => 'Ready']);
        $this->ensure($db, 'CONTAINER_STATUS', 'STATUS_ID', 50, ['STATUS_NAME' => 'Assembling']);
        $this->ensure($db, 'CONTAINER_STATUS', 'STATUS_ID', 70, ['STATUS_NAME' => 'Sent']);

        // PRODUCT_STATUS — REV_CONST.productStatus*
        $this->ensure($db, 'PRODUCT_STATUS', 'STATUS_ID', 1, ['STATUS_NAME' => 'Active']);
        $this->ensure($db, 'PRODUCT_STATUS', 'STATUS_ID', 2, ['STATUS_NAME' => 'Not Active']);
        $this->ensure($db, 'PRODUCT_STATUS', 'STATUS_ID', 3, ['STATUS_NAME' => 'Invisible']);

        // PRODUCT_TYPE — REV_CONST.goodType*
        $this->ensure($db, 'PRODUCT_TYPE', 'TYPE_ID', 2, ['TYPE_NAME' => 'Composite']);
        $this->ensure($db, 'PRODUCT_TYPE', 'TYPE_ID', 3, ['TYPE_NAME' => 'Primary']);
        $this->ensure($db, 'PRODUCT_TYPE', 'TYPE_ID', 4, ['TYPE_NAME' => 'Service']);

        // CATEGORY_STATUS — REV_CONST.categoryStatus*
        $this->ensure($db, 'CATEGORY_STATUS', 'STATUS_ID', 1, ['STATUS_NAME' => 'Active']);
        $this->ensure($db, 'CATEGORY_STATUS', 'STATUS_ID', 2, ['STATUS_NAME' => 'Not Active']);

        // CATEGORY_TYPE — в REV_CONST константы для этой таблицы не найдены,
        // id=1 придуман для теста (не подтверждено на реальной БД).
        $this->ensure($db, 'CATEGORY_TYPE', 'TYPE_ID', 1, ['TYPE_NAME' => 'Course (test)']);

        // CHANNEL_TYPE / CHANNEL_STATUS — REV_CONST.channelType*/channelStatus*
        $this->ensure($db, 'CHANNEL_TYPE', 'TYPE_ID', 1, ['TYPE_NAME' => 'Channel']);
        $this->ensure($db, 'CHANNEL_STATUS', 'STATUS_ID', 1, ['STATUS_NAME' => 'Active']);

        // BATCH_STATUS — REV_CONST.batchStatus*
        $this->ensure($db, 'BATCH_STATUS', 'STATUS_ID', 1, ['STATUS_NAME' => 'Active']);
        $this->ensure($db, 'BATCH_STATUS', 'STATUS_ID', 2, ['STATUS_NAME' => 'Closed']);

        // MONEY_ACCOUNT — REV_CONST.mdAccount* (полный набор, не только 40/70)
        $this->ensure($db, 'MONEY_ACCOUNT', 'TYPE_ID', 10, ['TYPE_NAME' => 'World']);
        $this->ensure($db, 'MONEY_ACCOUNT', 'TYPE_ID', 15, ['TYPE_NAME' => 'Transit']);
        $this->ensure($db, 'MONEY_ACCOUNT', 'TYPE_ID', 40, ['TYPE_NAME' => 'Invoice']);
        $this->ensure($db, 'MONEY_ACCOUNT', 'TYPE_ID', 50, ['TYPE_NAME' => 'Return']);
        $this->ensure($db, 'MONEY_ACCOUNT', 'TYPE_ID', 60, ['TYPE_NAME' => 'Loss']);
        $this->ensure($db, 'MONEY_ACCOUNT', 'TYPE_ID', 70, ['TYPE_NAME' => 'Material']);

        // MONEY_SOURCE_TYPE — в REV_CONST нет имён типов оплаты, только статусы (см. ниже).
        // TYPE_CODE/TYPE_NAME придуманы для теста.
        $this->ensure($db, 'MONEY_SOURCE_TYPE', 'TYPE_ID', 1, [
            'TYPE_CODE' => 'CARD',
            'TYPE_NAME' => 'Оплата картой (тест)',
        ]);

        // MONEY_SOURCE_STATUS — REV_CONST.msStatus*
        $this->ensure($db, 'MONEY_SOURCE_STATUS', 'STATUS_ID', 1, ['STATUS_NAME' => 'Active']);
        $this->ensure($db, 'MONEY_SOURCE_STATUS', 'STATUS_ID', 2, ['STATUS_NAME' => 'Not Active']);

        // EMSG_TYPE / EMSG_STATUS — константы REV_CONST для них не нашлись, значения
        // взяты из CASE-логики самого view API_MESSAGES_SMS_DAILY/API_MESSAGES_DAILY
        // (EMSG_TYPE: 1=SMS, 2=EMAIL; EMSG_STATUS=1 используется как "готово к отправке").
        $this->ensure($db, 'EMSG_TYPE', 'EMSG_TYPE', 1, ['EMSG_NAME' => 'SMS']);
        $this->ensure($db, 'EMSG_TYPE', 'EMSG_TYPE', 2, ['EMSG_NAME' => 'EMAIL']);
        $this->ensure($db, 'EMSG_STATUS', 'STATUS', 1, ['STATUS_NAME' => 'Ready']);
        $this->ensure($db, 'EMSG_STATUS', 'STATUS', 2, ['STATUS_NAME' => 'Try']);
        $this->ensure($db, 'EMSG_STATUS', 'STATUS', 3, ['STATUS_NAME' => 'Operator']);

        // Стаб-записи (не справочники по сути, но такие же статичные "заглушки",
        // на которые ссылаются CLIENT_BASKET.CHANNEL_ID/SALE_ID и CONTAINER.BATCH_ID
        // по умолчанию) — id взяты из REV_CONST.channelStub (-1) и
        // REV_CONST.batchDefaultNumber (-1).
        $this->ensure($db, 'CHANNEL', 'CHANNEL_ID', -1, [
            'TYPE_ID' => 1,
            'STATUS_ID' => 1,
            'CHANNEL_DATE' => now(),
            'CHANNEL_CODE' => 'STUB',
            'CHANNEL_NAME' => 'Channel stub (REV_CONST.channelStub)',
            'ISSUE_USEDATE' => 0,
            'ISSUE_SCOPE' => 1, // REV_CONST.channelScopePublic
            'ISSUE_RULE' => 1,  // REV_CONST.channelRuleReplace
        ]);
        $this->ensure($db, 'BATCH', 'BATCH_ID', -1, [
            'BATCH_DATE' => now(),
            'STATUS_ID' => 1,
            'BATCH_DESC' => 'Batch stub (REV_CONST.batchDefaultNumber)',
        ]);

        if ($this->command) {
            $this->command->info('Справочники и стаб-записи (CHANNEL -1, BATCH -1) готовы.');
        }
    }

    protected function ensure(ConnectionInterface $db, string $table, string $keyColumn, $keyValue, array $extra): void
    {
        $exists = $db->table($table)->where($keyColumn, $keyValue)->exists();

        if (!$exists) {
            $db->table($table)->insert(array_merge([$keyColumn => $keyValue], $extra));
        }
    }
}

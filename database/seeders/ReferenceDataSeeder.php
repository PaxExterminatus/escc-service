<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\GuardsAgainstNonTestDatabase;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Стаб-записи, нужные для FK-целостности при заполнении тестовых данных (см.
 * TestClientDataSeeder) — CHANNEL_ID=-1 и BATCH_ID=-1, на которые по умолчанию ссылаются
 * CLIENT_BASKET.CHANNEL_ID/SALE_ID и CONTAINER.BATCH_ID. id — из REV_CONST.channelStub (-1)
 * и REV_CONST.batchDefaultNumber (-1). Плюс минимальная цепочка OBJECT_TYPE/OBJECT_STATUS/
 * OBJECT_CATALOGUE(obj_id=16) — нужна, чтобы вставить строку в OBJECT_CONTRACT (параметры
 * отправки заказа для CourseScheduleCalculator).
 *
 * Справочники (CLIENT_TYPE, CLIENT_STATUS, CLIENT_SUB_STATUS, CONTAINER_STATUS,
 * PRODUCT_STATUS, PRODUCT_TYPE, CATEGORY_STATUS, CATEGORY_TYPE, CHANNEL_TYPE, CHANNEL_STATUS,
 * BATCH_STATUS, MONEY_ACCOUNT, MONEY_SOURCE_TYPE, MONEY_SOURCE_STATUS, EMSG_TYPE, EMSG_STATUS)
 * раньше заполнялись здесь придуманными значениями — теперь загружаются реальными строками
 * с продакшена (victory) через `php artisan import:excel` (см. .det-шаблоны экспорта dbForge
 * и App\Console\Commands\ImportExcelReferenceData). Проверено: все id, на которые ссылается
 * TestClientDataSeeder (CATEGORY_TYPE=1, PRODUCT_TYPE=2/3, CLIENT_TYPE=1, CONTAINER_STATUS=70,
 * EMSG_TYPE=1 и т.д.), в реальных данных существуют — самому сидеру менять ничего не пришлось.
 * Если понадобится наполнить эти таблицы на НОВОЙ локальной копии без доступа к victory —
 * смотри git-историю этого файла до этой правки, там был набор ensure() под них.
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

        // Цепочка FK для OBJECT_CONTRACT (параметры отправки заказа, см.
        // CourseScheduleCalculator/TestClientDataSeeder::enroll): OBJECT_CONTRACT.TYPE_ID
        // ссылается на OBJECT_CATALOGUE.OBJ_ID, а та — на OBJECT_TYPE/OBJECT_STATUS. Локально
        // все три таблицы пустые (не входили в реальный экспорт справочников — это не
        // справочник, а generic-реестр типов легаси-платформы), поэтому стаб как и для
        // CHANNEL/BATCH выше. obj_id=16 — REV_CONST.objTypeBasket.
        $this->ensure($db, 'OBJECT_TYPE', 'TYPE_ID', 1, [
            'TYPE_NAME' => 'Object type stub',
        ]);
        $this->ensure($db, 'OBJECT_STATUS', 'STATUS_ID', 1, [
            'STATUS_NAME' => 'Active',
        ]);
        $this->ensure($db, 'OBJECT_CATALOGUE', 'OBJ_ID', 16, [
            'TYPE_ID' => 1,
            'STATUS_ID' => 1,
            'OBJ_CODE' => 'BASKET',
            'OBJ_NAME' => 'Basket',
            'OBJ_TITLE' => 'Basket (REV_CONST.objTypeBasket)',
        ]);

        if ($this->command) {
            $this->command->info('Стаб-записи (CHANNEL -1, BATCH -1) готовы.');
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

<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\GuardsAgainstNonTestDatabase;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Заполняет "основные" (не справочные) таблицы легаси-схемы тестовыми данными для двух
 * клиентов кабинета: один с непогашенным долгом (по одному из двух курсов не заплатил
 * вообще ничего), второй — без долга, но с оплатой в несколько платежей (чтобы история
 * была реальной историей, а не одной строкой). Покрывает то, что реально читают наши
 * API_* view (см. escc-service/app/Domain/Cabinet, .../App/Profile, .../EFront, .../Messages):
 *   CLIENT                                  — включая CLIENT_BIRTHDAY/CLIENT_SEX для Profile
 *   CLIENT_PROPERTY                         — телефон/email клиента
 *   CLIENT_BASKET, CLIENT_SUB, CONTAINER     — 2 курса на клиента, каждый свой заказ
 *   MONEY_DIST, MONEY_SOURCE                 — начисления, оплаты и записи-погашения
 *                                               (см. enroll() — COURSE_NAME в
 *                                               API_CLIENT_FINANCE_HISTORY находится через них)
 *   API_EFRONT_DATA                          — по записи на клиента
 *   EMSG                                     — по SMS на клиента, адресовано на его CLIENT_PROPERTY.CLIENT_MPHONE
 *
 * Курсы, на которые записываются тестовые клиенты, — РЕАЛЬНЫЕ (см. pickRealCourses(): два
 * самых недавно добавленных активных продукта из уже импортированного CATALOGUE, см. память
 * project_local_reference_data_import), а не выдуманные CATALOGUE-записи, как было раньше.
 * Реальный состав курса (какие уроки внутри — таблица CATALOGUE_CONTENT) сознательно не
 * импортирован (та же память, 1.26М строк) — поэтому у тестовых заказов ниже нет вложенных
 * уроков (CLIENT_BASKET для урока внутри контейнера просто не создаётся), честно, а не
 * выдуманными node_id.
 *
 * Требует, чтобы ReferenceDataSeeder уже был применён (иначе упадёт по FK):
 *   php artisan db:seed --class="Database\Seeders\ReferenceDataSeeder"
 *   php artisan db:seed --class="Database\Seeders\TestClientDataSeeder"
 *
 * Тестовые клиенты (TEST-DEBTOR-01, TEST-PAYER-01) при каждом запуске удаляются
 * (resetClient) и создаются заново — так повторный прогон всегда даёт состояние,
 * соответствующее текущему коду сидера, без ручных SQL-патчей.
 *
 * Только для локальной тестовой XE — см. GuardsAgainstNonTestDatabase::guardTestDatabaseOnly().
 */
class TestClientDataSeeder extends Seeder
{
    use GuardsAgainstNonTestDatabase;

    protected string $connection = 'oracle';

    public function run(): void
    {
        $this->guardTestDatabaseOnly($this->connection);
        $db = DB::connection($this->connection);

        // Оба клиента сначала полностью сбрасываются (в т.ч. их CLIENT_BASKET/CLIENT_SUB,
        // ссылавшиеся на старые фиктивные курсы) — только после этого можно безопасно
        // удалить сами фиктивные CATALOGUE-записи, ничем больше не занятые.
        $this->resetClient($db, 'TEST-DEBTOR-01');
        $this->resetClient($db, 'TEST-PAYER-01');
        $this->purgeFakeCatalogue($db);

        $catalogue = $this->pickRealCourses($db);

        $debtorId = $this->createClient($db, 'Иван', 'Должников', 'TEST-DEBTOR-01', sex: 1, birthday: now()->subYears(30));
        $this->createClientProperty($db, $debtorId, phone: '+375293225337', email: 'debtor.test@example.invalid');
        // Курс 1: оплатил только частично, двумя платежами. Курс 2: не заплатил вообще.
        $this->enroll($db, $debtorId, $catalogue['course1'], chargeSum: 300.00, payments: [
            ['sum' => 60.00, 'daysAgo' => 25, 'desc' => 'Оплата картой, часть 1 (тест)'],
            ['sum' => 40.00, 'daysAgo' => 15, 'desc' => 'Оплата картой, часть 2 (тест)'],
        ], sendDaysAgo: 5);
        $this->enroll($db, $debtorId, $catalogue['course2'], chargeSum: 150.00, payments: [], sendDaysAgo: 5);
        // Напоминание о долге — по формату из самого дампа (см. REV_MDEPAYMENTS.client_account_tdc:
        // "DOLG: "||CLIENT.GETTOTALDEBT||" bel.rub; KOD KLIENTA: "||CLCODE).
        $this->createSms($db, '+375293225337', 'DOLG: 350.00 bel.rub; KOD KLIENTA: TEST-DEBTOR-01 (тест)');

        $payerId = $this->createClient($db, 'Мария', 'Полноплатова', 'TEST-PAYER-01', sex: 0, birthday: now()->subYears(27));
        $this->createClientProperty($db, $payerId, phone: '+375293225337', email: 'payer.test@example.invalid');
        // Оба курса оплачены полностью, курс 1 — двумя платежами (демонстрирует именно "историю", а не одну строку).
        $this->enroll($db, $payerId, $catalogue['course1'], chargeSum: 300.00, payments: [
            ['sum' => 150.00, 'daysAgo' => 25, 'desc' => 'Оплата картой, часть 1 (тест)'],
            ['sum' => 150.00, 'daysAgo' => 15, 'desc' => 'Оплата картой, часть 2 (тест)'],
        ], sendDaysAgo: 2);
        $this->enroll($db, $payerId, $catalogue['course2'], chargeSum: 150.00, payments: [
            ['sum' => 150.00, 'daysAgo' => 10, 'desc' => 'Оплата картой (тест)'],
        ], sendDaysAgo: 2);
        $this->createSms($db, '+375293225337', 'Спасибо за оплату! Баланс: 0.00 bel.rub; KOD KLIENTA: TEST-PAYER-01 (тест)');

        if ($this->command) {
            $this->command->info("Курс 1: [{$catalogue['course1']}] {$catalogue['course1_name']}");
            $this->command->info("Курс 2: [{$catalogue['course2']}] {$catalogue['course2_name']}");
            $this->command->info("Debtor client_id = {$debtorId}: курс 1 — выставлено 300, оплачено 100 (2 платежа); курс 2 — выставлено 150, не оплачено. Итого долг 350.");
            $this->command->info("Payer  client_id = {$payerId}: курс 1 — выставлено 300, оплачено 300 (2 платежа); курс 2 — выставлено 150, оплачено 150. Долг 0.");
        }
    }

    /**
     * Два самых недавно добавленных реальных активных курса из уже импортированного каталога
     * (REC_DATE DESC — приоритет недавно добавленным, как попросили). ROWNUM, не FETCH FIRST —
     * локальная XE не понимает ANSI OFFSET/FETCH (см. CourseController::search()).
     *
     * @return array{course1: int, course1_name: string, course2: int, course2_name: string}
     */
    protected function pickRealCourses(ConnectionInterface $db): array
    {
        $rows = $db->select("
            SELECT * FROM (
                SELECT node_id, node_name
                FROM catalogue
                WHERE type_id = 2 AND status_id = 1
                ORDER BY rec_date DESC
            ) WHERE ROWNUM <= 2
        ");

        if (count($rows) < 2) {
            throw new \RuntimeException(
                'В локальном каталоге меньше двух реальных активных курсов (CATALOGUE, TYPE_ID=2, STATUS_ID=1) — '
                . 'проверь, что справочные данные импортированы (см. память project_local_reference_data_import).'
            );
        }

        return [
            'course1' => (int)$rows[0]->node_id,
            'course1_name' => $rows[0]->node_name,
            'course2' => (int)$rows[1]->node_id,
            'course2_name' => $rows[1]->node_name,
        ];
    }

    /**
     * Убирает фиктивные CATALOGUE/CATEGORY-записи прежней версии сидера (TEST-COURSE-*,
     * TEST-LESSON-*, тестовые категории-якоря) — теперь курсы берутся из реального каталога
     * (см. pickRealCourses()), эта выдуманная структура больше не нужна и не создаётся заново.
     * Вызывать после resetClient() для обоих тестовых клиентов — иначе на эти записи ещё
     * ссылается их CLIENT_BASKET.
     */
    protected function purgeFakeCatalogue(ConnectionInterface $db): void
    {
        $nodeIds = $db->table('CATALOGUE')
            ->where('NODE_CODE', 'like', 'TEST-COURSE-%')
            ->orWhere('NODE_CODE', 'like', 'TEST-LESSON-%')
            ->pluck('node_id');

        if ($nodeIds->isEmpty()) {
            return;
        }

        $db->table('PRODUCT_CATEGORY')->whereIn('NODE_ID', $nodeIds)->delete();
        $db->table('CATALOGUE')->whereIn('NODE_ID', $nodeIds)->delete();
        $db->table('CATEGORY')->whereIn('CATEGORY_CODE', ['TEST_ROOT', 'TEST_LESSON_CAT', 'TEST_CHILD'])->delete();
    }

    /**
     * Удаляет тестового клиента с данным CLIENT_CODE и всё, что на него ссылается
     * (в порядке, безопасном для FK) — если клиента с таким кодом нет, ничего не делает.
     * Вызывается перед createClient(), чтобы каждый прогон сидера пересоздавал клиента
     * заново, а не наследовал данные от более старой версии сидера.
     */
    protected function resetClient(ConnectionInterface $db, string $code): void
    {
        $clientId = $db->table('CLIENT')->where('CLIENT_CODE', $code)->value('CLIENT_ID');
        if (!$clientId) {
            return;
        }

        $phones = $db->table('CLIENT_PROPERTY')->where('CLIENT_ID', $clientId)->pluck('client_mphone')->filter();
        foreach ($phones as $phone) {
            $db->table('EMSG')->where('EMSG_ADDRESS', $phone)->delete();
        }

        $containerIds = $db->table('CONTAINER')->where('CLIENT_ID', $clientId)->pluck('container_id');
        $subItemIds = $db->table('CLIENT_SUB')->where('CLIENT_ID', $clientId)->pluck('item_id');

        // Реальные (не "condition-only") FK внутри этой цепочки образуют порядок
        // CLIENT_BASKET(урок, по CONTAINER_ID) -> CONTAINER -> CLIENT_SUB -> CLIENT_BASKET(курс, по ITEM_ID) —
        // проверено на живой БД (см. USER_CONSTRAINTS), CLIENT_BASKET одна таблица на обе роли,
        // поэтому удаляем её в два захода, до и после CONTAINER/CLIENT_SUB.
        $db->table('CLIENT_BASKET')->whereIn('CONTAINER_ID', $containerIds)->delete();
        $db->table('CONTAINER')->where('CLIENT_ID', $clientId)->delete();
        $db->table('CLIENT_SUB')->where('CLIENT_ID', $clientId)->delete();
        $db->table('CLIENT_BASKET')->whereIn('ITEM_ID', $subItemIds)->delete();

        $db->table('MONEY_DIST')->where('CLIENT_ID', $clientId)->delete();
        $db->table('MONEY_SOURCE')->where('CLIENT_ID', $clientId)->delete();
        $db->table('API_EFRONT_DATA')->where('CLIENT_ID', $clientId)->delete();
        $db->table('CLIENT_PROPERTY')->where('CLIENT_ID', $clientId)->delete();
        $db->table('CLIENT')->where('CLIENT_ID', $clientId)->delete();
    }

    /**
     * CLIENT_PROPERTY — контакты клиента (телефон/email), отдельная таблица от CLIENT
     * (PK = CLIENT_ID, 1:1). Ничего специфичного не требует — ни один из справочников,
     * только FK на CLIENT (ON DELETE CASCADE NOVALIDATE).
     */
    protected function createClientProperty(ConnectionInterface $db, int $clientId, string $phone, string $email): void
    {
        $db->table('CLIENT_PROPERTY')->insert([
            'CLIENT_ID' => $clientId,
            'CLIENT_PHONE' => $phone,
            'CLIENT_MPHONE' => $phone,
            'CLIENT_EMAIL' => $email,
        ]);
    }

    /**
     * EMSG не привязана к клиенту напрямую (нет столбца CLIENT_ID — адресация по
     * EMSG_ADDRESS), это общая очередь сообщений. Должна подхватываться
     * API_MESSAGES_SMS_DAILY (EMSG_TYPE=1, EMSG_STATUS=1, EMSG_DATE = сегодня).
     */
    protected function createSms(ConnectionInterface $db, string $phone, string $body): void
    {
        $streamId = (int)$db->selectOne('SELECT EMSG_STREAM_SEQ.NEXTVAL AS ID FROM DUAL')->id;
        $emsgId = (int)$db->selectOne('SELECT EMSG_SEQ.NEXTVAL AS ID FROM DUAL')->id;

        $db->table('EMSG')->insert([
            'EMSG_STREAM' => $streamId,
            'EMSG' => $emsgId,
            'EMSG_TYPE' => 1, // SMS, см. CASE в API_MESSAGES_SMS_DAILY
            'EMSG_TYPE_SUB' => 1, // GSM7, та же логика в самом view
            'EMSG_DATE' => now(),
            'EMSG_ADDRESS' => $phone,
            'EMSG_BODY' => $body,
            'EMSG_STATUS' => 1,
        ]);
    }

    /**
     * @param int $sex 1 = мужчина, 0 = женщина — см. App\Enums\SexEnum (escc-service),
     *                 значения не из REV_CONST, а из кода приложения (SexCast/SexEnum).
     */
    protected function createClient(
        ConnectionInterface $db,
        string $firstName,
        string $lastName,
        string $code,
        int $sex,
        Carbon $birthday
    ): int {
        $id = (int)$db->selectOne('SELECT S_CLIENT.NEXTVAL AS ID FROM DUAL')->id;

        $db->table('CLIENT')->insert([
            'CLIENT_ID' => $id,
            'TYPE_ID' => 1, // REV_CONST.clientTypeCustomer
            'STATUS_ID' => 1, // REV_CONST.clientStatusActive
            'CLIENT_CODE' => $code,
            'CLIENT_DATE' => now(),
            'CLIENT_NAME' => $firstName,
            'CLIENT_MIDDLE_NAME' => 'Тестовна',
            'CLIENT_LAST_NAME' => $lastName,
            'CLIENT_BIRTHDAY' => $birthday,
            'CLIENT_SEX' => $sex,
            'ZIPCODE' => '000000',
            'ADDRESS_LINE1' => 'Тестовый адрес (сгенерировано TestClientDataSeeder)',
            'REC_UPDATE' => 0,
            'REC_DATE' => now(),
        ]);

        // API_EFRONT_DATA.DATA_XML — тип XMLTYPE, через query builder не биндится,
        // поэтому одна строка "как есть" через statement().
        $db->statement(
            "INSERT INTO API_EFRONT_DATA (CLIENT_ID, DATA_DATE, DATA_XML) VALUES (?, SYSDATE, XMLTYPE(?))",
            [$id, "<eFront><client_id>{$id}</client_id><test>true</test></eFront>"]
        );

        return $id;
    }

    /**
     * Записывает клиента на один курс: корзина на курс + подписка + заказ (контейнер) +
     * дочерняя строка корзины — содержимое именно этого контейнера (CLIENT_BASKET иерархична:
     * PARENT_ID указывает на строку курса/подписки, см. ниже) + начисление + ноль/несколько
     * оплат (каждая — с записью-погашением, связывающей платёж с контейнером/курсом).
     *
     * @param array<array{sum: float, daysAgo: int, desc: string}> $payments
     */
    protected function enroll(
        ConnectionInterface $db,
        int $clientId,
        int $courseNodeId,
        float $chargeSum,
        array $payments,
        int $sendDaysAgo = 5
    ): void {
        $courseItemId = (int)$db->selectOne('SELECT S_CLIENT_BASKET.NEXTVAL AS ID FROM DUAL')->id;
        $db->table('CLIENT_BASKET')->insert([
            'ITEM_ID' => $courseItemId,
            'CHANNEL_ID' => -1, // REV_CONST.channelStub, см. ReferenceDataSeeder
            'SALE_ID' => -1,
            'NODE_ID' => $courseNodeId,
            'ITEM_PRICE' => $chargeSum,
            'ITEM_COST' => $chargeSum,
            'ITEM_DISCOUNT' => 0,
            'ITEM_STATUS' => 1, // REV_CONST.itemStatusActive
            'ITEM_MODE' => 0, // REV_CONST.itemModeRegular
            'REC_DATE' => now(),
        ]);

        $subId = (int)$db->selectOne('SELECT S_CLIENT_SUB.NEXTVAL AS ID FROM DUAL')->id;
        $db->table('CLIENT_SUB')->insert([
            'SUB_ID' => $subId,
            'CLIENT_ID' => $clientId,
            'STATUS_ID' => 1, // REV_CONST.subStatusActive
            'ITEM_ID' => $courseItemId,
            'PRODUCT_ID' => $courseNodeId,
            'START_DATE' => now()->subDays(30),
            'NEXT_DATE' => now()->subDays(30),
            'NEXT_UNIT' => 0,
            'MSG_ID' => 0,
            'STUD_CODE' => 'S' . $subId, // REV_CONST.subFirstSymbl = 'S'
            'MODE_SALE' => 2, // REV_CONST.boxModeSaleAtOnce
        ]);

        // Параметры отправки заказа (частота/день/уроков за раз) — не заполняется автоматически
        // нигде в схеме, легаси-форма пишет её сама при оформлении подписки. Без этой строки
        // CourseScheduleCalculator не может посчитать график (нет данных — не гадает), поэтому
        // сеятся тестовые значения: раз в месяц, 20-го числа, 1 урок за отправку.
        $db->table('OBJECT_CONTRACT')->insert([
            'TYPE_ID' => 16, // REV_CONST.objTypeBasket
            'OBJECT_ID' => $courseItemId,
            'P4' => 1, // SbsUnitsInOneShipment
            'P5' => '1 Month', // SbsFrequencyOfSending
            'P6' => '20', // SbsRecalculationDay
        ]);

        $containerId = (int)$db->selectOne('SELECT S_CONTAINER.NEXTVAL AS ID FROM DUAL')->id;
        $db->table('CONTAINER')->insert([
            'CONTAINER_ID' => $containerId,
            'CLIENT_ID' => $clientId,
            'STATUS_ID' => 70, // REV_CONST.boxStatusSent — иначе долг по контейнеру "не считается" (см. client_account_tdc)
            'CONTAINER_CODE' => 'T' . $containerId,
            'CONTAINER_COST' => $chargeSum,
            'CONTAINER_WEIGHT' => 0,
            'POST_VAL1' => 0,
            'POST_VAL2' => 0,
            'POST_VAL3' => 0,
            'POST_VAL4' => 0,
            'POST_VAL5' => 0,
            'POST_VAL6' => 0,
            'POST_FEE' => 2.50, // фактическая стоимость пересылки
            'POST_FEE_CLIENT' => 3.00, // выставлено клиенту за пересылку (см. ContainerController::finance)
            'POST_PACK' => 0,
            'CONTAINER_DATE' => now()->subDays(30),
            'SEND_DATE' => now()->subDays($sendDaysAgo),
            'MSG_ID' => 0,
            'MODE_THROW' => 0, // REV_CONST.boxModeThrowOther
            'MODE_SALE' => 2, // REV_CONST.boxModeSaleAtOnce
            'MODE_EVENT' => 0, // REV_CONST.boxModeEventOther
            'POST_SCHEMA' => 7, // REV_CONST.boxPostSchemaOnLine
            'BATCH_ID' => -1, // REV_CONST.batchDefaultNumber, см. ReferenceDataSeeder
            'SUB_ID' => $subId,
        ]);

        // Содержимое контейнера — дочерняя строка корзины (PARENT_ID = строка курса/подписки
        // выше, CONTAINER_ID = эта конкретная отправка). Реального состава курса (какие именно
        // уроки) не найти — CATALOGUE_CONTENT не импортирован (см. память
        // project_local_reference_data_import), поэтому честно ссылаемся на сам узел курса, а
        // не выдумываем несуществующие "уроки".
        $contentItemId = (int)$db->selectOne('SELECT S_CLIENT_BASKET.NEXTVAL AS ID FROM DUAL')->id;
        $db->table('CLIENT_BASKET')->insert([
            'ITEM_ID' => $contentItemId,
            'PARENT_ID' => $courseItemId,
            'CHANNEL_ID' => -1,
            'SALE_ID' => -1,
            'NODE_ID' => $courseNodeId,
            'CONTAINER_ID' => $containerId,
            'ITEM_PRICE' => $chargeSum,
            'ITEM_COST' => $chargeSum,
            'ITEM_DISCOUNT' => 0,
            'ITEM_STATUS' => 1,
            'ITEM_MODE' => 0,
            'REC_DATE' => now(),
        ]);

        $logId = (int)$db->selectOne('SELECT S_MONEY_DIST.NEXTVAL AS ID FROM DUAL')->id;
        $db->table('MONEY_DIST')->insert([
            'LOG_ID' => $logId,
            'CLIENT_ID' => $clientId,
            'TRANS_DATE' => now()->subDays(30),
            'TRANS_SUM' => $chargeSum,
            'DEBET' => 70, // REV_CONST.mdAccountMaterial
            'DEBET_ID' => 0, // не FK-проверяется, см. дамп схемы
            'CREDIT' => 40, // REV_CONST.mdAccountInvoice
            'CREDIT_ID' => $containerId, // сторона с кодом 40 хранит CONTAINER_ID (см. API_CLIENT_FINANCE_HISTORY)
        ]);

        foreach ($payments as $payment) {
            $transId = (int)$db->selectOne('SELECT S_MONEY_SOURCE.NEXTVAL AS ID FROM DUAL')->id;
            $db->table('MONEY_SOURCE')->insert([
                'TRANS_ID' => $transId,
                'CLIENT_ID' => $clientId,
                'TRANS_SUM' => $payment['sum'],
                'TRANS_DATE' => now()->subDays($payment['daysAgo']),
                'TRANS_DESC' => $payment['desc'],
                'TYPE_ID' => 1,
                'STATUS_ID' => 1, // REV_CONST.msStatusActive
                'REC_DATE' => now(),
            ]);

            // Запись-погашение: связывает платёж с контейнером через MTRANS_ID = MONEY_SOURCE.TRANS_ID
            // (см. API_CLIENT_FINANCE_HISTORY — так платёж находит свой курс).
            $settlementLogId = (int)$db->selectOne('SELECT S_MONEY_DIST.NEXTVAL AS ID FROM DUAL')->id;
            $db->table('MONEY_DIST')->insert([
                'LOG_ID' => $settlementLogId,
                'CLIENT_ID' => $clientId,
                'TRANS_DATE' => now()->subDays($payment['daysAgo']),
                'TRANS_SUM' => $payment['sum'],
                'MTRANS_ID' => $transId,
                'DEBET' => 40, // REV_CONST.mdAccountInvoice
                'DEBET_ID' => $containerId,
                'CREDIT' => 15, // REV_CONST.mdAccountTransit
                'CREDIT_ID' => 0,
            ]);
        }
    }
}

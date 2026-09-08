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
 *   CATALOGUE, CATEGORY, PRODUCT_CATEGORY    — 2 тестовых курса, по 1-2 урока, категории
 *   MONEY_DIST, MONEY_SOURCE                 — начисления и НЕСКОЛЬКО оплат (API_CLIENT_FINANCE_HISTORY)
 *   API_EFRONT_DATA                          — по записи на клиента
 *   EMSG                                     — по SMS на клиента, адресовано на его CLIENT_PROPERTY.CLIENT_MPHONE
 *
 * Требует, чтобы ReferenceDataSeeder уже был применён (иначе упадёт по FK):
 *   php artisan db:seed --class="Database\Seeders\ReferenceDataSeeder"
 *   php artisan db:seed --class="Database\Seeders\TestClientDataSeeder"
 *
 * Идемпотентно и для клиентов (ensureClient по CLIENT_CODE), и для общего тестового
 * каталога курсов/уроков/категорий — повторный запуск (например, после сбоя на середине)
 * не создаёт дублей.
 *
 * Только для локальной тестовой XE — см. GuardsAgainstNonTestDatabase::guardTestDatabaseOnly().
 */
class TestClientDataSeeder extends Seeder
{
    use GuardsAgainstNonTestDatabase;

    protected string $connection = 'oracle';

    // Категория из API_CLIENT_COURSES_LESSONS: "... CATEGORY_ID IN (3, 4, 11053)".
    // Берём 3 как якорь для тестовых уроков.
    protected const LESSON_CATEGORY_ID = 3;

    // Категория из API_COURSES_CATEGORY: "... PARENT_ID IN (14592, 14712, 14953)".
    // Берём 14592 как якорь для тестового дерева категорий курса.
    protected const COURSE_ROOT_CATEGORY_ID = 14592;

    public function run(): void
    {
        $this->guardTestDatabaseOnly($this->connection);
        $db = DB::connection($this->connection);

        $catalogue = $this->ensureTestCatalogue($db);

        [$debtorId, $debtorCreated] = $this->ensureClient($db, 'Иван', 'Должников', 'TEST-DEBTOR-01', sex: 1, birthday: now()->subYears(30));
        if ($debtorCreated) {
            $this->createClientProperty($db, $debtorId, phone: '+375291112233', email: 'debtor.test@example.invalid');
            // Курс 1: оплатил только частично, двумя платежами. Курс 2: не заплатил вообще.
            $this->enroll($db, $debtorId, $catalogue['course1'], $catalogue['course1_lessons'], chargeSum: 300.00, payments: [
                ['sum' => 60.00, 'daysAgo' => 25, 'desc' => 'Оплата картой, часть 1 (тест)'],
                ['sum' => 40.00, 'daysAgo' => 15, 'desc' => 'Оплата картой, часть 2 (тест)'],
            ]);
            $this->enroll($db, $debtorId, $catalogue['course2'], $catalogue['course2_lessons'], chargeSum: 150.00, payments: []);
            // Напоминание о долге — по формату из самого дампа (см. REV_MDEPAYMENTS.client_account_tdc:
            // "DOLG: "||CLIENT.GETTOTALDEBT||" bel.rub; KOD KLIENTA: "||CLCODE).
            $this->createSms($db, '+375291112233', 'DOLG: 350.00 bel.rub; KOD KLIENTA: TEST-DEBTOR-01 (тест)');
        }

        [$payerId, $payerCreated] = $this->ensureClient($db, 'Мария', 'Полноплатова', 'TEST-PAYER-01', sex: 0, birthday: now()->subYears(27));
        if ($payerCreated) {
            $this->createClientProperty($db, $payerId, phone: '+375291112244', email: 'payer.test@example.invalid');
            // Оба курса оплачены полностью, курс 1 — двумя платежами (демонстрирует именно "историю", а не одну строку).
            $this->enroll($db, $payerId, $catalogue['course1'], $catalogue['course1_lessons'], chargeSum: 300.00, payments: [
                ['sum' => 150.00, 'daysAgo' => 25, 'desc' => 'Оплата картой, часть 1 (тест)'],
                ['sum' => 150.00, 'daysAgo' => 15, 'desc' => 'Оплата картой, часть 2 (тест)'],
            ]);
            $this->enroll($db, $payerId, $catalogue['course2'], $catalogue['course2_lessons'], chargeSum: 150.00, payments: [
                ['sum' => 150.00, 'daysAgo' => 10, 'desc' => 'Оплата картой (тест)'],
            ]);
            $this->createSms($db, '+375291112244', 'Спасибо за оплату! Баланс: 0.00 bel.rub; KOD KLIENTA: TEST-PAYER-01 (тест)');
        }

        if ($this->command) {
            $this->command->info("Debtor client_id = {$debtorId}: курс 1 — выставлено 300, оплачено 100 (2 платежа); курс 2 — выставлено 150, не оплачено. Итого долг 350.");
            $this->command->info("Payer  client_id = {$payerId}: курс 1 — выставлено 300, оплачено 300 (2 платежа); курс 2 — выставлено 150, оплачено 150. Долг 0.");
        }
    }

    /**
     * Общий (не привязанный к конкретному клиенту) тестовый каталог: 2 курса + категории,
     * с id категорий, зашитыми в WHERE наших API_* view. Идемпотентно — при повторном
     * запуске не дублирует, если CATEGORY/CATALOGUE с этими кодами уже есть.
     *
     * @return array{course1: int, course1_lessons: int[], course2: int, course2_lessons: int[]}
     */
    protected function ensureTestCatalogue(ConnectionInterface $db): array
    {
        // Категория-якорь для API_CLIENT_COURSES_LESSONS
        if (!$db->table('CATEGORY')->where('CATEGORY_ID', self::LESSON_CATEGORY_ID)->exists()) {
            $db->table('CATEGORY')->insert([
                'CATEGORY_ID' => self::LESSON_CATEGORY_ID,
                'TYPE_ID' => 1,
                'CATEGORY_CODE' => 'TEST_LESSON_CAT',
                'CATEGORY_NAME' => 'Тестовая категория уроков',
                'STATUS_ID' => 1,
            ]);
        }

        // Категория-якорь (родитель) для API_COURSES_CATEGORY
        if (!$db->table('CATEGORY')->where('CATEGORY_ID', self::COURSE_ROOT_CATEGORY_ID)->exists()) {
            $db->table('CATEGORY')->insert([
                'CATEGORY_ID' => self::COURSE_ROOT_CATEGORY_ID,
                'TYPE_ID' => 1,
                'CATEGORY_CODE' => 'TEST_ROOT',
                'CATEGORY_NAME' => 'Тестовые курсы (корень)',
                'STATUS_ID' => 1,
            ]);
        }

        $childCategoryId = $db->table('CATEGORY')->where('CATEGORY_CODE', 'TEST_CHILD')->value('CATEGORY_ID');
        if (!$childCategoryId) {
            $childCategoryId = (int)$db->selectOne('SELECT S_CATEGORY.NEXTVAL AS ID FROM DUAL')->id;
            $db->table('CATEGORY')->insert([
                'CATEGORY_ID' => $childCategoryId,
                'PARENT_ID' => self::COURSE_ROOT_CATEGORY_ID,
                'TYPE_ID' => 1,
                'CATEGORY_CODE' => 'TEST_CHILD',
                'CATEGORY_NAME' => 'Тестовый курс: категория (дочерняя)',
                'STATUS_ID' => 1,
            ]);
        }

        $course1 = $this->ensureCourse($db, $childCategoryId, 'TEST-COURSE-1', 'Основы бухгалтерии', [
            'TEST-LESSON-1' => 'Урок 1. Введение',
            'TEST-LESSON-2' => 'Урок 2. Баланс',
        ]);

        $course2 = $this->ensureCourse($db, $childCategoryId, 'TEST-COURSE-2', 'Основы права', [
            'TEST-LESSON-3' => 'Урок 1. Источники права',
        ]);

        return [
            'course1' => $course1['node_id'],
            'course1_lessons' => $course1['lessons'],
            'course2' => $course2['node_id'],
            'course2_lessons' => $course2['lessons'],
        ];
    }

    /**
     * @return array{node_id: int, lessons: int[]}
     */
    protected function ensureCourse(ConnectionInterface $db, int $categoryId, string $courseCode, string $courseName, array $lessons): array
    {
        $existingCourse = $db->table('CATALOGUE')->where('NODE_CODE', $courseCode)->first();
        if ($existingCourse) {
            $lessonIds = $db->table('CATALOGUE')
                ->whereIn('NODE_CODE', array_keys($lessons))
                ->orderBy('NODE_CODE')
                ->pluck('node_id')
                ->map(fn ($id) => (int)$id)
                ->all();

            return ['node_id' => (int)$existingCourse->node_id, 'lessons' => $lessonIds];
        }

        $courseNodeId = (int)$db->selectOne('SELECT S_CATALOGUE.NEXTVAL AS ID FROM DUAL')->id;
        $db->table('CATALOGUE')->insert([
            'NODE_ID' => $courseNodeId,
            'TYPE_ID' => 2, // REV_CONST.goodTypeComposite — курс состоит из уроков
            'STATUS_ID' => 1, // REV_CONST.productStatusActive
            'NODE_CODE' => $courseCode,
            'NODE_NAME' => 'Тестовый курс: ' . $courseName,
            'NODE_ALT_NAME' => $courseName, // это поле отдаёт API_CLIENT_COURSES.NAME
        ]);
        $db->table('PRODUCT_CATEGORY')->insert([
            'CATEGORY_ID' => $categoryId,
            'NODE_ID' => $courseNodeId,
        ]);

        $lessonIds = [];
        foreach ($lessons as $code => $name) {
            $nodeId = (int)$db->selectOne('SELECT S_CATALOGUE.NEXTVAL AS ID FROM DUAL')->id;
            $db->table('CATALOGUE')->insert([
                'NODE_ID' => $nodeId,
                'TYPE_ID' => 3, // REV_CONST.goodTypePrimary
                'STATUS_ID' => 1,
                'NODE_CODE' => $code,
                'NODE_NAME' => $name,
                // CHECK PRIMARYWAREHOUSECODE требует WAREHOUSE_CODE NOT NULL при TYPE_ID=3
                'WAREHOUSE_CODE' => 'NO_WH_CODE', // REV_CONST.goodNoWarehouseCode
            ]);
            $db->table('PRODUCT_CATEGORY')->insert([
                'CATEGORY_ID' => self::LESSON_CATEGORY_ID,
                'NODE_ID' => $nodeId,
            ]);
            $lessonIds[] = $nodeId;
        }

        return ['node_id' => $courseNodeId, 'lessons' => $lessonIds];
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
     * EMSG_ADDRESS), это общая очередь сообщений. Формат текста — по образцу из самого
     * дампа (см. вызов в REV_MDEPAYMENTS). Должна подхватываться API_MESSAGES_SMS_DAILY
     * (EMSG_TYPE=1, EMSG_STATUS=1, EMSG_DATE = сегодня). Идемпотентно — ищем по
     * EMSG_ADDRESS+EMSG_BODY перед вставкой.
     */
    protected function createSms(ConnectionInterface $db, string $phone, string $body): void
    {
        // EMSG_BODY — CLOB, Oracle не сравнивает его через "=" (ORA-00932), поэтому
        // идемпотентность проверяем только по адресу (для теста этого достаточно —
        // один телефон = один клиент = одна SMS).
        $exists = $db->table('EMSG')->where('EMSG_ADDRESS', $phone)->exists();

        if ($exists) {
            return;
        }

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
     * Идемпотентно по CLIENT_CODE (уникальный индекс XAK1CLIENT) — при повторном запуске
     * (например, после сбоя на более позднем шаге) не создаёт клиента заново, а возвращает
     * его существующий id и created=false, чтобы вызывающий код не дублировал зачисления/SMS.
     *
     * @param int $sex 1 = мужчина, 0 = женщина — см. App\Enums\SexEnum (escc-service),
     *                 значения не из REV_CONST, а из кода приложения (SexCast/SexEnum).
     * @return array{0: int, 1: bool} [client_id, created]
     */
    protected function ensureClient(
        ConnectionInterface $db,
        string $firstName,
        string $lastName,
        string $code,
        int $sex,
        Carbon $birthday
    ): array {
        $existingId = $db->table('CLIENT')->where('CLIENT_CODE', $code)->value('CLIENT_ID');
        if ($existingId) {
            return [(int)$existingId, false];
        }

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

        return [$id, true];
    }

    /**
     * Записывает клиента на один курс: корзина на курс + подписка + заказ (контейнер) +
     * корзина на уроки внутри этого заказа + начисление + ноль/несколько оплат.
     *
     * @param int[] $lessonNodeIds
     * @param array<array{sum: float, daysAgo: int, desc: string}> $payments
     */
    protected function enroll(
        ConnectionInterface $db,
        int $clientId,
        int $courseNodeId,
        array $lessonNodeIds,
        float $chargeSum,
        array $payments
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
            'POST_FEE' => 0,
            'POST_FEE_CLIENT' => 0,
            'POST_PACK' => 0,
            'CONTAINER_DATE' => now()->subDays(30),
            'MSG_ID' => 0,
            'MODE_THROW' => 0, // REV_CONST.boxModeThrowOther
            'MODE_SALE' => 2, // REV_CONST.boxModeSaleAtOnce
            'MODE_EVENT' => 0, // REV_CONST.boxModeEventOther
            'POST_SCHEMA' => 7, // REV_CONST.boxPostSchemaOnLine
            'BATCH_ID' => -1, // REV_CONST.batchDefaultNumber, см. ReferenceDataSeeder
            'SUB_ID' => $subId,
        ]);

        foreach ($lessonNodeIds as $lessonNodeId) {
            $lessonItemId = (int)$db->selectOne('SELECT S_CLIENT_BASKET.NEXTVAL AS ID FROM DUAL')->id;
            $db->table('CLIENT_BASKET')->insert([
                'ITEM_ID' => $lessonItemId,
                'CHANNEL_ID' => -1,
                'SALE_ID' => -1,
                'NODE_ID' => $lessonNodeId,
                'CONTAINER_ID' => $containerId,
                'ITEM_PRICE' => 0,
                'ITEM_DISCOUNT' => 0,
                'ITEM_STATUS' => 1,
                'ITEM_MODE' => 0,
                'REC_DATE' => now(),
            ]);
        }

        $logId = (int)$db->selectOne('SELECT S_MONEY_DIST.NEXTVAL AS ID FROM DUAL')->id;
        $db->table('MONEY_DIST')->insert([
            'LOG_ID' => $logId,
            'CLIENT_ID' => $clientId,
            'TRANS_DATE' => now()->subDays(30),
            'TRANS_SUM' => $chargeSum,
            'DEBET' => 70, // REV_CONST.mdAccountMaterial
            'DEBET_ID' => 0, // не FK-проверяется, см. дамп схемы
            'CREDIT' => 40, // REV_CONST.mdAccountInvoice
            'CREDIT_ID' => 0,
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
        }
    }
}

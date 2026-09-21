<?php

namespace App\Domain\App\Course\Services;

use Illuminate\Support\Facades\DB;

/**
 * Баланс по курсу (сумме контейнеров подписки) или по одному контейнеру — начислено,
 * оплачено, возвращено, потери, и итоговый баланс (сколько ещё должен клиент).
 *
 * Копия логики легаси-пакета CRS_PKG_UTILS (REVELATION 20260904 1518.sql, ~строка 75417) —
 * не вызывает сам пакет, потому что часть его функций (CRS_DEBT_SUM/CLIENT_DEBT_SUM) ссылается
 * на несуществующий в локальной копии схемы тип OBJ$CLIENT, из-за чего ВЕСЬ пакет становится
 * INVALID и не компилируется целиком — даже те функции, что сами по себе не зависят от OBJ$.
 * Тот же приём уже применён в database/oracle/views/API_CLIENT_FINANCE_HISTORY.sql — чистый
 * SQL по MONEY_DIST/MONEY_SOURCE вместо PL/SQL-пакетов, тестируется на локальной копии.
 *
 * MONEY_DIST — проводки с двумя сторонами (DEBET/CREDIT, коды счетов из REV_CONST.mdAccount*):
 *   40 — Invoice (счёт клиента), 50 — Return (возврат), 60 — Loss (потеря/компенсация),
 *   70 — Material (материалы, куда относится начисление).
 * Тип проводки (платёж/возврат/потеря) различается не по коду счёта, а по тому, какая из
 * колонок MTRANS_ID/RTRANS_ID/LTRANS_ID заполнена — см. каждый метод ниже.
 *
 * Знак: начисление (INVOICED) кредитует счёт 40 (увеличивает долг); оплата/возврат/потеря
 * дебетуют счёт 40 (уменьшают долг) — поэтому у всех трёх одна и та же форма запроса
 * (DEBET=40 → +сумма, CREDIT=40 → -сумма), только другой парный счёт и колонка *TRANS_ID.
 * БАЛАНС = НАЧИСЛЕНО − ОПЛАЧЕНО − ВОЗВРАЩЕНО − ПОТЕРИ (сколько всё ещё должен клиент).
 *
 * forClient() — та же формула на уровне клиента целиком (все его контейнеры, а не один курс).
 * Раньше {amount}-тег (см. TagResolver) получал долг клиента напрямую из
 * API_SERVICE_ACCOUNT.ACCOUNT_CLIENT_TOTAL_DEB — эта функция на локальной копии схемы валит
 * PLS-00302 ('USERID' must be declared, см. project_local_xe_debt_function_broken), так что
 * панель, где {amount} реально нужен (предпросмотр/отправка письма), падала. Инлайним ту же
 * формулу, что и для курса/контейнера, просто без фильтра по SUB_ID.
 */
class CourseBalanceCalculator
{
    protected const ACCOUNT_INVOICE = 40;
    protected const ACCOUNT_RETURN = 50;
    protected const ACCOUNT_LOSS = 60;
    protected const ACCOUNT_MATERIAL = 70;

    /**
     * @param int $subId Курс (CLIENT_SUB.SUB_ID)
     * @param int|null $containerId Один контейнер курса либо null — сумма по всем контейнерам курса
     * @return array{invoiced: float, received: float, returned: float, lost: float, balance: float}
     */
    public function forCourse(int $subId, ?int $containerId = null): array
    {
        return $this->calculate(['sub_id' => $subId], $containerId);
    }

    /**
     * @param int $clientId
     * @return array{invoiced: float, received: float, returned: float, lost: float, balance: float}
     */
    public function forClient(int $clientId): array
    {
        return $this->calculate(['client_id' => $clientId], null);
    }

    /**
     * @param array{sub_id: int}|array{client_id: int} $scope
     */
    protected function calculate(array $scope, ?int $containerId): array
    {
        $invoiced = $this->invoicedSum($scope, $containerId);
        $received = $this->receivedSum($scope, $containerId);
        $returned = $this->returnedSum($scope, $containerId);
        $lost = $this->lostSum($scope, $containerId);

        return [
            'invoiced' => $invoiced,
            'received' => $received,
            'returned' => $returned,
            'lost' => $lost,
            'balance' => $invoiced - $received - $returned - $lost,
        ];
    }

    /** Контейнеры, за которые вообще выставлен счёт (CONTAINER_COST > 0) — общая часть всех сумм ниже */
    protected function scopeCte(array $scope, ?int $containerId, array &$bindings): string
    {
        if (isset($scope['sub_id'])) {
            $scopeFilter = 'cnt.sub_id = :subId';
            $bindings['subId'] = $scope['sub_id'];
        } else {
            $scopeFilter = 'cnt.client_id = :clientId';
            $bindings['clientId'] = $scope['client_id'];
        }

        $filter = '';

        if ($containerId !== null) {
            $filter = 'AND cnt.container_id = :containerId';
            $bindings['containerId'] = $containerId;
        }

        return "SELECT cnt.container_id, cnt.client_id FROM container cnt
                 WHERE {$scopeFilter} AND cnt.container_cost > 0 {$filter}";
    }

    /** Начислено (сумма выставленных счетов) — единственная сумма без обратной проводки */
    protected function invoicedSum(array $scope, ?int $containerId): float
    {
        $bindings = [];
        $scopeCte = $this->scopeCte($scope, $containerId, $bindings);

        $row = DB::connection('oracle')->selectOne("
            WITH b AS ({$scopeCte})
            SELECT NVL(SUM(mdd.trans_sum), 0) AS total
              FROM money_dist mdd, b
             WHERE mdd.credit = :accountInvoice
               AND mdd.debet = :accountMaterial
               AND mdd.credit_id = b.container_id
               AND mdd.client_id = b.client_id
        ", $bindings + ['accountInvoice' => self::ACCOUNT_INVOICE, 'accountMaterial' => self::ACCOUNT_MATERIAL]);

        return (float) $row->total;
    }

    /** Оплачено реальными деньгами (MTRANS_ID — проводка платежа из MONEY_SOURCE) */
    protected function receivedSum(array $scope, ?int $containerId): float
    {
        return $this->reversibleSum($scope, $containerId, null, 'mtrans_id');
    }

    /** Возвращено клиенту (RTRANS_ID), счёт 40 ↔ 50 */
    protected function returnedSum(array $scope, ?int $containerId): float
    {
        return $this->reversibleSum($scope, $containerId, self::ACCOUNT_RETURN, 'rtrans_id');
    }

    /** Списано как потеря/компенсация (LTRANS_ID), счёт 40 ↔ 60 */
    protected function lostSum(array $scope, ?int $containerId): float
    {
        return $this->reversibleSum($scope, $containerId, self::ACCOUNT_LOSS, 'ltrans_id');
    }

    /**
     * Общая форма для received/returned/lost: DEBET=40 (свой counterAccount, если задан) → +сумма,
     * CREDIT=40 (тот же counterAccount) → -сумма, различаются только по *TRANS_ID и, для
     * received, отсутствием ограничения на парный счёт (платёж мог прийти с любого счёта).
     */
    protected function reversibleSum(array $scope, ?int $containerId, ?int $counterAccount, string $transIdColumn): float
    {
        $bindings = [];
        $scopeCte = $this->scopeCte($scope, $containerId, $bindings);
        $counterFilterDebet = $counterAccount !== null ? 'AND mdd.credit = :counterAccount' : '';
        $counterFilterCredit = $counterAccount !== null ? 'AND mdd.debet = :counterAccount' : '';

        $bindings['accountInvoice'] = self::ACCOUNT_INVOICE;

        // Плейсхолдер :counterAccount добавляем в биндинги только когда он реально есть в
        // тексте запроса — OCI8 (в отличие от других драйверов) падает на "лишнем", ни разу не
        // упомянутом биндинге с ORA-01036.
        if ($counterAccount !== null) {
            $bindings['counterAccount'] = $counterAccount;
        }

        $row = DB::connection('oracle')->selectOne("
            WITH b AS ({$scopeCte})
            SELECT NVL(SUM(total), 0) AS total FROM (
                SELECT mdd.trans_sum AS total
                  FROM money_dist mdd, b
                 WHERE mdd.debet = :accountInvoice {$counterFilterDebet}
                   AND mdd.debet_id = b.container_id
                   AND mdd.client_id = b.client_id
                   AND mdd.{$transIdColumn} IS NOT NULL
                UNION ALL
                SELECT -mdd.trans_sum AS total
                  FROM money_dist mdd, b
                 WHERE mdd.credit = :accountInvoice {$counterFilterCredit}
                   AND mdd.credit_id = b.container_id
                   AND mdd.client_id = b.client_id
                   AND mdd.{$transIdColumn} IS NOT NULL
            )
        ", $bindings);

        // Отрицательный итог (больше "минусовых" проводок, чем "плюсовых") на практике не
        // встречается у легаси-функций (там тоже отрицательное значение округляется до нуля) —
        // повторяем то же округление, чтобы не показывать отрицательную "оплату".
        return max(0.0, (float) $row->total);
    }
}

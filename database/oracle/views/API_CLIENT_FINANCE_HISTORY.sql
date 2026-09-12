--
-- Представление "API_CLIENT_FINANCE_HISTORY"
-- Финансовая история клиента: что и когда было выставлено (начислено) и оплачено.
-- READ ONLY view поверх легаси-таблиц REVELATION, без изменения исходных данных.
--
-- Источники:
--   PAYMENT (оплачено)  -> MONEY_SOURCE (поступления от клиента) + MONEY_SOURCE_TYPE (расшифровка типа)
--   CHARGE  (выставлено) -> MONEY_DIST (проводка "выставление счёта" в бухгалтерском журнале клиента)
--
-- MONEY_DIST.CREDIT/MONEY_DIST.DEBET — это значения MONEY_ACCOUNT.TYPE_ID (см. REV_CONST:
-- mdAccountWorld=10, mdAccountTransit=15, mdAccountInvoice=40, mdAccountReturn=50,
-- mdAccountLoss=60, mdAccountMaterial=70). Комбинация CREDIT=40 (Invoice) + DEBET=70
-- (Material) идентифицирует проводку "выставление счёта" (CHARGE) — в MONEY_DIST попадают
-- только реально проведённые бухгалтерские проводки, отменённые/ошибочные CONTAINER туда
-- не пишутся.
--
-- Статусы — из пакета констант REV_CONST (секция "Money source Status"):
--   money_source.status_id: 1 = msStatusActive (подтверждён), 2 = msStatusNotActive.
--   Константы пакета недоступны напрямую из SQL (PL/SQL package constant, не функция),
--   поэтому коды захардкожены — при изменении REV_CONST это нужно поправить и здесь вручную.
--
-- Известные ограничения:
--   1. ID не гарантированно уникален между CHARGE и PAYMENT (LOG_ID и TRANS_ID — разные
--      последовательности) — не критично для списка, но не годится как ключ для find-по-id.
--   2. Коды возвратов/потерь/компенсаций ('4010', '1015', '1510', '5040', '4050', '6040',
--      '4060') в выборку не входят — вьюха покрывает только "выставлено"/"оплачено".
--
-- COURSE_NAME: ни MONEY_DIST, ни MONEY_SOURCE не хранят курс напрямую — связь через
--   CONTAINER. Сторона проводки с кодом 40 (Invoice) — это и есть CONTAINER_ID в
--   соответствующей _ID-колонке. Для CHARGE (CREDIT=40 всегда, зафиксировано в WHERE) —
--   это md.CREDIT_ID. Для PAYMENT прямой связи нет — платёж находит свой контейнер через
--   запись-погашение в MONEY_DIST: MONEY_DIST.MTRANS_ID = MONEY_SOURCE.TRANS_ID. Дальше в
--   обоих случаях: CONTAINER.SUB_ID -> CLIENT_SUB cs JOIN CLIENT_BASKET cb ON cs.ITEM_ID =
--   cb.ITEM_ID JOIN CATALOGUE ct ON cb.NODE_ID = ct.NODE_ID.
--

DROP VIEW API_CLIENT_FINANCE_HISTORY;
-- ^ если представления ещё нет — Oracle вернёт ORA-00942 "table or view does not exist",
--   это ожидаемо при первом создании, ошибку можно игнорировать.

CREATE VIEW API_CLIENT_FINANCE_HISTORY (
  ID,
  CLIENT_ID,
  OPERATION_TYPE,
  OPERATION_DATE,
  AMOUNT,
  DESCRIPTION,
  COURSE_NAME
) AS
    SELECT ms.TRANS_ID                        AS ID,
           ms.CLIENT_ID                       AS CLIENT_ID,
           'PAYMENT'                          AS OPERATION_TYPE,
           ms.TRANS_DATE                      AS OPERATION_DATE,
           ms.TRANS_SUM                       AS AMOUNT,
           NVL(mst.TYPE_NAME, ms.TRANS_DESC)  AS DESCRIPTION,
           pay_cat.NODE_ALT_NAME              AS COURSE_NAME
      FROM MONEY_SOURCE ms
        LEFT JOIN MONEY_SOURCE_TYPE mst
          ON mst.TYPE_ID = ms.TYPE_ID
        LEFT JOIN MONEY_DIST pay_md
          ON pay_md.MTRANS_ID = ms.TRANS_ID
         AND (pay_md.DEBET = 40 OR pay_md.CREDIT = 40)
        LEFT JOIN CONTAINER pay_cn
          ON pay_cn.CONTAINER_ID = CASE WHEN pay_md.DEBET = 40 THEN pay_md.DEBET_ID
                                         ELSE pay_md.CREDIT_ID END
        LEFT JOIN CLIENT_SUB pay_cs
          ON pay_cs.SUB_ID = pay_cn.SUB_ID
        LEFT JOIN CLIENT_BASKET pay_cb
          ON pay_cb.ITEM_ID = pay_cs.ITEM_ID
        LEFT JOIN CATALOGUE pay_cat
          ON pay_cat.NODE_ID = pay_cb.NODE_ID
      WHERE ms.STATUS_ID = 1 -- REV_CONST.msStatusActive

    UNION ALL

    SELECT md.LOG_ID                          AS ID,
           md.CLIENT_ID                       AS CLIENT_ID,
           'CHARGE'                           AS OPERATION_TYPE,
           md.TRANS_DATE                      AS OPERATION_DATE,
           md.TRANS_SUM                       AS AMOUNT,
           'Выставление счёта'                AS DESCRIPTION,
           chg_cat.NODE_ALT_NAME              AS COURSE_NAME
      FROM MONEY_DIST md
        LEFT JOIN CONTAINER chg_cn
          ON chg_cn.CONTAINER_ID = md.CREDIT_ID
        LEFT JOIN CLIENT_SUB chg_cs
          ON chg_cs.SUB_ID = chg_cn.SUB_ID
        LEFT JOIN CLIENT_BASKET chg_cb
          ON chg_cb.ITEM_ID = chg_cs.ITEM_ID
        LEFT JOIN CATALOGUE chg_cat
          ON chg_cat.NODE_ID = chg_cb.NODE_ID
      WHERE md.CREDIT = 40 AND md.DEBET = 70 -- см. UK_Client_Debt: код '4070'

      ORDER BY 4 DESC
      WITH READ ONLY;

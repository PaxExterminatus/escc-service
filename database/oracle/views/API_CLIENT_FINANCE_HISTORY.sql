--
-- Представление "API_CLIENT_FINANCE_HISTORY"
-- Финансовая история клиента: что и когда было выставлено (начислено) и оплачено.
-- Сделано по образцу существующих API_* представлений (см. API_CLIENT, API_CLIENT_COURSES) —
-- простой READ ONLY view поверх легаси-таблиц REVELATION, без изменения исходных данных.
--
-- Источники:
--   PAYMENT (оплачено)  -> MONEY_SOURCE (поступления от клиента) + MONEY_SOURCE_TYPE (расшифровка типа)
--   CHARGE  (выставлено) -> MONEY_DIST (проводка "выставление счёта" в бухгалтерском журнале клиента)
--
-- ПЕРЕСМОТРЕНО после находки функции UK_Client_Debt (создаёт официальный расчёт долга клиента,
-- используется в проде) — она содержит расшифровку кодов MONEY_DIST.CREDIT/MONEY_DIST.DEBET
-- (это значения MONEY_ACCOUNT.TYPE_ID, см. REV_CONST: mdAccountWorld=10, mdAccountTransit=15,
-- mdAccountInvoice=40, mdAccountReturn=50, mdAccountLoss=60, mdAccountMaterial=70):
--   CREDIT=40 (Invoice) + DEBET=70 (Material) => 'выставление счёта' (CHARGE) -- взято отсюда
--   CREDIT=10 (World)   + DEBET=40 (Invoice)  => 'оплата посылки'    (PAYMENT, дублирует MONEY_SOURCE)
-- Это надёжнее прежнего варианта (raw CONTAINER.CONTAINER_COST без фильтра по статусу) —
-- в MONEY_DIST попадают только реально проведённые бухгалтерские проводки, отменённые/ошибочные
-- CONTAINER туда не пишутся (подтверждается тем, что в легаси-коде долг по контейнеру считается
-- значимым только при CONTAINER.STATUS_ID = REV_CONST.boxStatusSent (70), см. client_account_tdc
-- в пакете REV_MDEPAYMENTS).
--
-- Статусы взяты из пакета констант REV_CONST (см. секцию "Money source Status" в его спецификации):
--   money_source.status_id: 1 = msStatusActive (подтверждён), 2 = msStatusNotActive
--   Константы пакета недоступны напрямую из SQL (PL/SQL package constant, не функция),
--   поэтому коды ниже захардкожены — при изменении REV_CONST это нужно поправить и здесь вручную.
--
-- ВАЖНО, желательно перепроверить на живой БД перед использованием в проде:
--   1. ID в представлении не гарантированно уникален между CHARGE и PAYMENT (LOG_ID и TRANS_ID —
--      разные последовательности). Для устойчивого использования как первичного ключа в Eloquent-модели
--      уникальность в рамках операции не критична (список, не find-по-id), но если понадобится —
--      можно заменить на конкатенацию типа: 'P' || TRANS_ID / 'C' || LOG_ID (VARCHAR2).
--   2. Не проверено на реальных данных, что комбинация CREDIT=40/DEBET=70 в MONEY_DIST достаточна —
--      UK_Client_Debt использует ещё несколько кодов (потеря материалов, возвраты, компенсации:
--      '4010', '1015', '1510', '5040', '4050', '6040', '4060'), которые сюда сознательно не включены,
--      т.к. они не про "выставлено/оплачено", а про списания/возвраты/компенсации — если нужна
--      более полная картина (например, вкладка истории должна показывать и возвраты) — стоит
--      обсудить отдельно, добавлять их вслепую не стал.
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
  DESCRIPTION
) AS
    SELECT ms.TRANS_ID                        AS ID,
           ms.CLIENT_ID                       AS CLIENT_ID,
           'PAYMENT'                          AS OPERATION_TYPE,
           ms.TRANS_DATE                      AS OPERATION_DATE,
           ms.TRANS_SUM                       AS AMOUNT,
           NVL(mst.TYPE_NAME, ms.TRANS_DESC)  AS DESCRIPTION
      FROM MONEY_SOURCE ms
        LEFT JOIN MONEY_SOURCE_TYPE mst
          ON mst.TYPE_ID = ms.TYPE_ID
      WHERE ms.STATUS_ID = 1 -- REV_CONST.msStatusActive

    UNION ALL

    SELECT md.LOG_ID                          AS ID,
           md.CLIENT_ID                       AS CLIENT_ID,
           'CHARGE'                           AS OPERATION_TYPE,
           md.TRANS_DATE                      AS OPERATION_DATE,
           md.TRANS_SUM                       AS AMOUNT,
           'Выставление счёта'                AS DESCRIPTION
      FROM MONEY_DIST md
      WHERE md.CREDIT = 40 AND md.DEBET = 70 -- см. UK_Client_Debt: код '4070'

      ORDER BY 4 DESC
      WITH READ ONLY;

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Добавляет COURSE_NAME в API_CLIENT_FINANCE_HISTORY.
 *
 * Ни MONEY_DIST, ни MONEY_SOURCE не хранят курс напрямую — связь идёт через CONTAINER:
 *   CHARGE:   CONTAINER_ID = md.CREDIT_ID (WHERE в этой view уже фиксирует CREDIT=40,
 *             а по всей легаси-логике (см. `... CASE WHEN md.debet = 40 THEN md.DEBET_ID
 *             WHEN md.credit = 40 THEN md.CREDIT_ID END AS CONTAINER_ID` в дампе схемы)
 *             сторона с кодом 40 (Invoice) — это и есть CONTAINER_ID).
 *   PAYMENT:  сама MONEY_SOURCE ссылки на CONTAINER не имеет; связь — через
 *             MONEY_DIST.MTRANS_ID = MONEY_SOURCE.TRANS_ID (запись-погашение), и в ней —
 *             та же логика "сторона = 40 -> CONTAINER_ID".
 * Дальше: CONTAINER.SUB_ID -> CLIENT_SUB.PRODUCT_ID -> CATALOGUE.NODE_ID,
 * CATALOGUE.NODE_ALT_NAME — то же поле, что отдаёт API_CLIENT_COURSES.NAME.
 *
 * database/oracle/views/API_CLIENT_FINANCE_HISTORY.sql держим в синхроне с этой миграцией.
 */
class AddCourseNameToApiClientFinanceHistoryView extends Migration
{
    protected $connection = 'oracle';

    public function up()
    {
        DB::connection($this->connection)->unprepared(<<<SQL
            CREATE OR REPLACE FORCE VIEW API_CLIENT_FINANCE_HISTORY (
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
                    LEFT JOIN CATALOGUE pay_cat
                      ON pay_cat.NODE_ID = pay_cs.PRODUCT_ID
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
                      ON chg_cn.CONTAINER_ID = md.CREDIT_ID -- CREDIT=40 зафиксировано ниже
                    LEFT JOIN CLIENT_SUB chg_cs
                      ON chg_cs.SUB_ID = chg_cn.SUB_ID
                    LEFT JOIN CATALOGUE chg_cat
                      ON chg_cat.NODE_ID = chg_cs.PRODUCT_ID
                  WHERE md.CREDIT = 40 AND md.DEBET = 70 -- см. UK_Client_Debt: код '4070'

                  ORDER BY 4 DESC
                  WITH READ ONLY
            SQL);
    }

    public function down()
    {
        DB::connection($this->connection)->unprepared(<<<SQL
            CREATE OR REPLACE FORCE VIEW API_CLIENT_FINANCE_HISTORY (
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
                  WHERE ms.STATUS_ID = 1

                UNION ALL

                SELECT md.LOG_ID                          AS ID,
                       md.CLIENT_ID                       AS CLIENT_ID,
                       'CHARGE'                           AS OPERATION_TYPE,
                       md.TRANS_DATE                      AS OPERATION_DATE,
                       md.TRANS_SUM                       AS AMOUNT,
                       'Выставление счёта'                AS DESCRIPTION
                  FROM MONEY_DIST md
                  WHERE md.CREDIT = 40 AND md.DEBET = 70

                  ORDER BY 4 DESC
                  WITH READ ONLY
            SQL);
    }
}

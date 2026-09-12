<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Представление "API_CLIENT_FINANCE_HISTORY" — финансовая история клиента
 * (что и когда было выставлено и оплачено).
 *
 * Подробное описание источников (MONEY_SOURCE, MONEY_DIST), обоснование выбора кодов
 * CREDIT/DEBET и пути COURSE_NAME — в database/oracle/views/API_CLIENT_FINANCE_HISTORY.sql,
 * тот файл держим в синхроне с этой миграцией как читаемый чистый SQL для ручного
 * применения DBA при необходимости.
 */
class CreateApiClientFinanceHistoryView extends Migration
{
    /**
     * Представление живёт в легаси-схеме REVELATION, поэтому явно указываем connection,
     * даже если сейчас он совпадает со значением по умолчанию.
     *
     * @var string
     */
    protected $connection = 'oracle';

    public function up()
    {
        DB::connection($this->connection)->unprepared(<<<SQL
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
                      ON chg_cn.CONTAINER_ID = md.CREDIT_ID -- CREDIT=40 зафиксировано ниже
                    LEFT JOIN CLIENT_SUB chg_cs
                      ON chg_cs.SUB_ID = chg_cn.SUB_ID
                    LEFT JOIN CLIENT_BASKET chg_cb
                      ON chg_cb.ITEM_ID = chg_cs.ITEM_ID
                    LEFT JOIN CATALOGUE chg_cat
                      ON chg_cat.NODE_ID = chg_cb.NODE_ID
                  WHERE md.CREDIT = 40 AND md.DEBET = 70 -- см. UK_Client_Debt: код '4070'

                  ORDER BY 4 DESC
                  WITH READ ONLY
            SQL);
    }

    public function down()
    {
        DB::connection($this->connection)->unprepared('DROP VIEW API_CLIENT_FINANCE_HISTORY');
    }
}

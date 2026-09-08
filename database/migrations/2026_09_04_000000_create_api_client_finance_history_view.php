<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Представление "API_CLIENT_FINANCE_HISTORY" — финансовая история клиента
 * (что и когда было выставлено и оплачено), по образцу API_CLIENT/API_CLIENT_COURSES.
 *
 * Подробное описание источников (MONEY_SOURCE, MONEY_DIST) и обоснование выбора
 * кодов CREDIT/DEBET (см. функцию UK_Client_Debt) — в
 * database/oracle/views/API_CLIENT_FINANCE_HISTORY.sql, тот файл держим в синхроне
 * с этой миграцией как читаемый чистый SQL для ручного применения DBA при необходимости.
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

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection($this->connection)->unprepared(<<<SQL
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
                  WITH READ ONLY
            SQL);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection($this->connection)->unprepared('DROP VIEW API_CLIENT_FINANCE_HISTORY');
    }
}

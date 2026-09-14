<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * PK генерируется на стороне приложения: MessageTemplate расширяет
 * Yajra\Oci8\Eloquent\OracleEloquent и объявляет $sequence = 'OPERATOR_MESSAGE_TEMPLATE_SEQ' —
 * Eloquent сам получает значение из последовательности перед INSERT, триггер для этого не нужен.
 */
class CreateOperatorMessageTemplateTable extends Migration
{
    protected $connection = 'oracle';

    public function up(): void
    {
        DB::connection($this->connection)->unprepared(<<<SQL
            CREATE TABLE OPERATOR_MESSAGE_TEMPLATE (
              TEMPLATE_ID NUMBER PRIMARY KEY,
              CODE        VARCHAR2(64 BYTE)  NOT NULL,
              NAME        VARCHAR2(255 BYTE) NOT NULL,
              BODY        CLOB               NOT NULL,
              IS_ACTIVE   NUMBER(1, 0)       DEFAULT 1 NOT NULL,
              CONSTRAINT UK_MSG_TEMPLATE_CODE UNIQUE (CODE)
            )
            SQL);

        DB::connection($this->connection)->unprepared(<<<SQL
            CREATE SEQUENCE OPERATOR_MESSAGE_TEMPLATE_SEQ START WITH 1 INCREMENT BY 1
            SQL);
    }

    public function down(): void
    {
        DB::connection($this->connection)->unprepared('DROP SEQUENCE OPERATOR_MESSAGE_TEMPLATE_SEQ');
        DB::connection($this->connection)->unprepared('DROP TABLE OPERATOR_MESSAGE_TEMPLATE');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Единая таблица шаблонов — заменяет собой OPERATOR_MESSAGE_TEMPLATE (текст SMS/Email) и
 * DOCUMENT_TEMPLATE (docx для печати): по сути одна и та же сущность "шаблон", разные
 * носители (см. App\Domain\Templates\Enums\TemplateTypeEnum).
 *
 * PK генерируется на стороне приложения: Template расширяет Yajra\Oci8\Eloquent\OracleEloquent
 * и объявляет $sequence = 'SERVICE_TEMPLATE_SEQ' — Eloquent сам получает значение из
 * последовательности перед INSERT, триггер для этого не нужен.
 *
 * OPERATION_ID (App\Domain\Templates\Enums\TemplateOperationEnum) — NULL для обычных
 * самостоятельных шаблонов сообщений (выбираются оператором вручную при отправке) и для
 * обёрток писем (TYPE_ID = wrapper — свободная библиотека, не привязана к операции), задан —
 * для шаблонов, привязанных к операции (счёт как документ/как письмо).
 *
 * UK_SERVICE_TEMPLATE_OP_TYPE сделан функциональным индексом, а не обычным UNIQUE(OPERATION_ID,
 * TYPE_ID): Oracle индексирует составной уникальный ключ, если хотя бы ОДНА из колонок не NULL —
 * то есть обычный UNIQUE(OPERATION_ID, TYPE_ID) запретил бы больше одного свободного шаблона
 * (или больше одной обёртки) с одинаковым TYPE_ID, т.к. (NULL, 1) считается дублем (NULL, 1).
 * Функция сводит ОБЕ колонки к NULL, когда OPERATION_ID IS NULL, — тогда вся пара пропускается
 * индексом (Oracle не индексирует строки, где ВСЕ колонки NULL), и ограничение реально работает
 * только для настоящих операций.
 *
 * WRAPPER_ID — у text/html-контента (свободный шаблон сообщения или "Счёт по email") ссылка на
 * конкретную обёртку письма (SERVICE_TEMPLATE.TYPE_ID = wrapper); NULL — использовать обёртку с
 * IS_DEFAULT = 1 (см. EmailComposer::wrap). IS_DEFAULT — среди обёрток ровно одна может быть
 * такой (обеспечивается в TemplateController, не в БД).
 *
 * WRAPPER_AUTO — у text/html-контента: игнорировать WRAPPER_ID и выбирать обёртку в момент
 * отправки по фактическому балансу клиента (тег {amount}) — долг → обёртка с WRAPPER_ROLE=debt,
 * без долга → с WRAPPER_ROLE=positive (см. EmailComposer::wrapperForBalance). WRAPPER_ROLE — у
 * обёрток: какой стороне баланса она соответствует; NULL — обёртка не участвует в авто-выборе
 * (например нейтральная "по умолчанию").
 */
class CreateTemplateTable extends Migration
{
    protected $connection = 'oracle';

    public function up(): void
    {
        DB::connection($this->connection)->unprepared(<<<SQL
            CREATE TABLE SERVICE_TEMPLATE (
              TEMPLATE_ID  NUMBER PRIMARY KEY,
              TYPE_ID      NUMBER             NOT NULL,
              OPERATION_ID NUMBER,
              CODE         VARCHAR2(64 BYTE)  NOT NULL,
              NAME         VARCHAR2(255 BYTE) NOT NULL,
              BODY         CLOB,
              FILENAME     VARCHAR2(255 BYTE),
              WRAPPER_ID   NUMBER,
              WRAPPER_AUTO NUMBER(1, 0)       DEFAULT 0 NOT NULL,
              WRAPPER_ROLE NUMBER,
              IS_DEFAULT   NUMBER(1, 0)       DEFAULT 0 NOT NULL,
              IS_ACTIVE    NUMBER(1, 0)       DEFAULT 1 NOT NULL,
              UPLOADED_AT  DATE,
              CONSTRAINT UK_SERVICE_TEMPLATE_CODE UNIQUE (CODE),
              CONSTRAINT FK_SERVICE_TEMPLATE_WRAPPER FOREIGN KEY (WRAPPER_ID) REFERENCES SERVICE_TEMPLATE (TEMPLATE_ID)
            )
            SQL);

        DB::connection($this->connection)->unprepared(<<<SQL
            CREATE UNIQUE INDEX UK_SERVICE_TEMPLATE_OP_TYPE ON SERVICE_TEMPLATE (
              CASE WHEN OPERATION_ID IS NOT NULL THEN OPERATION_ID END,
              CASE WHEN OPERATION_ID IS NOT NULL THEN TYPE_ID END
            )
            SQL);

        DB::connection($this->connection)->unprepared(<<<SQL
            CREATE SEQUENCE SERVICE_TEMPLATE_SEQ START WITH 1 INCREMENT BY 1
            SQL);
    }

    public function down(): void
    {
        DB::connection($this->connection)->unprepared('DROP SEQUENCE SERVICE_TEMPLATE_SEQ');
        DB::connection($this->connection)->unprepared('DROP TABLE SERVICE_TEMPLATE');
    }
}

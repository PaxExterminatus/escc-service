<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Хранит только переопределения расписания — какие задачи вообще есть, задаёт
 * App\Domain\Scheduler\Enums\ScheduledTaskEnum, здесь только время запуска и признак
 * включённости, настроенные через UI (см. App\Domain\Scheduler\Controllers\ScheduledTaskController).
 * Пока задача не настраивалась вручную, строки нет — действует значение по умолчанию из enum.
 *
 * TASK_ID — не суррогатный автоинкремент, а само значение ScheduledTaskEnum, поэтому
 * последовательность не создаём (см. ScheduledTaskSchedule::$incrementing = false).
 */
class CreateScheduledTaskTable extends Migration
{
    protected $connection = 'oracle';

    public function up(): void
    {
        DB::connection($this->connection)->unprepared(<<<SQL
            CREATE TABLE SERVICE_SCHEDULED_TASK (
              TASK_ID    NUMBER PRIMARY KEY,
              RUN_TIME   VARCHAR2(5 BYTE) NOT NULL,
              IS_ENABLED NUMBER(1, 0)     DEFAULT 1 NOT NULL
            )
            SQL);
    }

    public function down(): void
    {
        DB::connection($this->connection)->unprepared('DROP TABLE SERVICE_SCHEDULED_TASK');
    }
}

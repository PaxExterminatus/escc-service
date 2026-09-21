<?php

namespace App\Domain\Scheduler\Models;

use Yajra\Oci8\Eloquent\OracleEloquent;

/**
 * Переопределение расписания одной задачи из ScheduledTaskEnum — время запуска и признак
 * включённости. Пока строки нет, действуют значения по умолчанию из самого enum (см.
 * ScheduledTaskController::index(), App\Console\Kernel::schedule()).
 *
 * TASK_ID — не суррогатный автоинкремент, а само значение ScheduledTaskEnum (набор задач задаёт
 * код, а не БД), поэтому $incrementing = false и своя последовательность не нужна.
 */
class ScheduledTaskSchedule extends OracleEloquent
{
    protected $table = 'SERVICE_SCHEDULED_TASK';
    protected $primaryKey = 'task_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['task_id', 'run_time', 'is_enabled'];

    protected function casts(): array
    {
        return [
            'task_id' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }
}

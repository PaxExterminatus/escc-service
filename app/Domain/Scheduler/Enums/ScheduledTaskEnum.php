<?php

namespace App\Domain\Scheduler\Enums;

/**
 * Фиксированный список задач, которые можно поставить в расписание — какие задачи есть,
 * задаёт этот enum, а время запуска и признак включённости настраиваются через UI и хранятся
 * в App\Domain\Scheduler\Models\ScheduledTaskSchedule (см. App\Console\Kernel::schedule(),
 * который объединяет то и другое при построении реального расписания).
 */
enum ScheduledTaskEnum: int
{
    case dailyMessages = 1;

    public function label(): string
    {
        return match ($this) {
            self::dailyMessages => 'Ежедневная рассылка (SMS/Email)',
        };
    }

    /** Artisan-сигнатура, которую вызывает Schedule::command() */
    public function command(): string
    {
        return match ($this) {
            self::dailyMessages => 'messages:send-daily',
        };
    }

    /** Время запуска (ЧЧ:ММ), пока задача не настроена явно через ScheduledTaskSchedule */
    public function defaultTime(): string
    {
        return match ($this) {
            self::dailyMessages => '09:00',
        };
    }
}

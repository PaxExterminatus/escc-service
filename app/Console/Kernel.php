<?php

namespace App\Console;

use App\Domain\Scheduler\Enums\ScheduledTaskEnum;
use App\Domain\Scheduler\Models\ScheduledTaskSchedule;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Время запуска и признак включённости настраиваются через UI (см.
        // App\Domain\Scheduler\Controllers\ScheduledTaskController) и хранятся в
        // ScheduledTaskSchedule; сам набор задач задаёт ScheduledTaskEnum. Пока задача не
        // настраивалась вручную, строки нет — используется значение по умолчанию из enum.
        $rows = ScheduledTaskSchedule::all()->keyBy('task_id');

        foreach (ScheduledTaskEnum::cases() as $task) {
            $row = $rows->get($task->value);

            if ($row && ! $row->is_enabled) {
                continue;
            }

            $schedule->command($task->command())->dailyAt($row->run_time ?? $task->defaultTime());
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

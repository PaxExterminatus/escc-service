<?php

namespace App\Domain\Scheduler\Controllers;

use App\Domain\Scheduler\Enums\ScheduledTaskEnum;
use App\Domain\Scheduler\Models\ScheduledTaskSchedule;
use App\Domain\Scheduler\Requests\UpdateScheduledTaskRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ScheduledTaskController extends Controller
{
    /** Полный список задач (см. ScheduledTaskEnum) с их текущим расписанием */
    public function index(): JsonResponse
    {
        $rows = ScheduledTaskSchedule::all()->keyBy('task_id');

        $tasks = collect(ScheduledTaskEnum::cases())->map(function (ScheduledTaskEnum $task) use ($rows) {
            $row = $rows->get($task->value);

            return [
                'task_id' => $task->value,
                'label' => $task->label(),
                'command' => $task->command(),
                'run_time' => $row->run_time ?? $task->defaultTime(),
                'is_enabled' => $row?->is_enabled ?? true,
            ];
        });

        return response()->json(['tasks' => $tasks]);
    }

    public function update(int $taskId, UpdateScheduledTaskRequest $request): JsonResponse
    {
        $task = ScheduledTaskEnum::tryFrom($taskId);
        abort_if($task === null, 404, 'Неизвестная задача.');

        $data = $request->validated();

        ScheduledTaskSchedule::updateOrCreate(
            ['task_id' => $task->value],
            ['run_time' => $data['run_time'], 'is_enabled' => $data['is_enabled']]
        );

        return response()->json(['status' => 'ok']);
    }
}

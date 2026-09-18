<?php

namespace App\Domain\App\Course\Controllers;

use App\Domain\App\Container\Models\Container;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Курсы (подписки CLIENT_SUB) клиента и контейнеры (посылки) по конкретному курсу —
 * для навигации "клиент → курсы → контейнеры" в профиле и в пикере тегов уровня "Счёт".
 */
class CourseController extends Controller
{
    /**
     * Курсы клиента
     */
    public function index(int $clientId): JsonResponse
    {
        $rows = DB::connection('oracle')->select(
            'SELECT cs.sub_id, cat.node_name AS course_name, cs.next_date
             FROM client_sub cs
             JOIN catalogue cat ON cat.node_id = cs.product_id
             WHERE cs.client_id = :clientId
             ORDER BY cs.start_date DESC',
            ['clientId' => $clientId]
        );

        $courses = collect($rows)->map(fn ($row) => [
            'sub_id' => (int) $row->sub_id,
            'course_name' => $row->course_name,
            'next_send_date' => $row->next_date ? Carbon::parse($row->next_date)->format('d.m.Y') : null,
        ]);

        return response()->json(['courses' => $courses]);
    }

    /**
     * Контейнеры (посылки) по курсу (подписке)
     */
    public function containers(int $subId): JsonResponse
    {
        $containers = Container::where('sub_id', $subId)
            ->orderByDesc('container_date')
            ->get();

        $rows = $containers->map(fn (Container $container) => [
            'container_id' => $container->container_id,
            'container_code' => $container->container_code,
            'status' => $container->status_id, // строка-лейбл, см. ContainerStatusCast
            'container_cost' => (float) $container->container_cost,
            'send_date' => $container->send_date?->format('d.m.Y'),
        ]);

        return response()->json(['containers' => $rows]);
    }
}

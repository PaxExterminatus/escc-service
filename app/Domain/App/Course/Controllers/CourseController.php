<?php

namespace App\Domain\App\Course\Controllers;

use App\Domain\App\Container\Models\Container;
use App\Domain\App\Course\Requests\SearchCourseRequest;
use App\Domain\App\Course\Services\CourseBalanceCalculator;
use App\Domain\App\Course\Services\CourseScheduleCalculator;
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
    public function __construct(
        protected CourseBalanceCalculator $balance,
        protected CourseScheduleCalculator $schedule,
    ) {
    }

    /**
     * Один курс (подписка) — для страницы курса, по аналогии со страницей контейнера
     */
    public function show(int $subId): JsonResponse
    {
        $row = DB::connection('oracle')->selectOne(
            'SELECT cs.sub_id, cs.client_id, cat.node_name AS course_name, css.status_name AS status,
                    cs.start_date, cs.next_date
               FROM client_sub cs
               JOIN catalogue cat ON cat.node_id = cs.product_id
               LEFT JOIN client_sub_status css ON css.status_id = cs.status_id
              WHERE cs.sub_id = :subId',
            ['subId' => $subId]
        );

        abort_if($row === null, 404, 'Курс не найден.');

        return response()->json(['course' => [
            'sub_id' => (int) $row->sub_id,
            'client_id' => (int) $row->client_id,
            'course_name' => $row->course_name,
            'status' => $row->status,
            'start_date' => $row->start_date ? Carbon::parse($row->start_date)->format('d.m.Y') : null,
            'next_send_date' => $row->next_date ? Carbon::parse($row->next_date)->format('d.m.Y') : null,
            ...$this->balance->forCourse($subId),
        ]]);
    }

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
            ...$this->balance->forCourse((int) $row->sub_id),
        ]);

        return response()->json(['courses' => $courses]);
    }

    /**
     * Поиск курсов (подписок) по набору необязательных параметров, с пагинацией.
     *
     * CLIENT_SUB читается напрямую (не Eloquent-модель, см. show()/index() выше), поэтому
     * пагинация — вручную: свой COUNT(*) и OFFSET/FETCH, а не Model::paginate(). Форма ответа
     * та же самая, что у Eloquent-пагинации (data/current_page/last_page/per_page/total) —
     * фронтенду не важно, как страница получена, лишь бы контракт был одинаковым.
     */
    public function search(SearchCourseRequest $request): JsonResponse
    {
        $perPage = 50;
        $page = max(1, $request->integer('page', 1));
        $offset = ($page - 1) * $perPage;

        $conditions = [];
        $bindings = [];

        if ($v = $request->sub_code) {
            $conditions[] = 'cs.sub_code LIKE :subCode';
            $bindings['subCode'] = "%{$v}%";
        }

        if ($v = $request->client_id) {
            $conditions[] = 'cs.client_id = :clientId';
            $bindings['clientId'] = $v;
        }

        if ($v = $request->client_name) {
            $conditions[] = '(cl.client_name LIKE :clientName OR cl.client_last_name LIKE :clientName)';
            $bindings['clientName'] = "%{$v}%";
        }

        if ($v = $request->course_name) {
            $conditions[] = 'cat.node_name LIKE :courseName';
            $bindings['courseName'] = "%{$v}%";
        }

        if ($v = $request->status_id) {
            $conditions[] = 'cs.status_id = :statusId';
            $bindings['statusId'] = $v;
        }

        if ($v = $request->start_date_from) {
            $conditions[] = "TRUNC(cs.start_date) >= TO_DATE(:startFrom, 'YYYY-MM-DD')";
            $bindings['startFrom'] = $v;
        }

        if ($v = $request->start_date_to) {
            $conditions[] = "TRUNC(cs.start_date) <= TO_DATE(:startTo, 'YYYY-MM-DD')";
            $bindings['startTo'] = $v;
        }

        $where = $conditions ? 'WHERE '.implode(' AND ', $conditions) : '';

        $from = "FROM client_sub cs
                 JOIN catalogue cat ON cat.node_id = cs.product_id
                 LEFT JOIN client_sub_status css ON css.status_id = cs.status_id
                 LEFT JOIN client cl ON cl.client_id = cs.client_id
                 {$where}";

        $total = (int) DB::connection('oracle')->selectOne("SELECT COUNT(*) AS cnt {$from}", $bindings)->cnt;

        // ANSI OFFSET/FETCH ловит ORA-00933 прямо на OFFSET — локальная XE его не понимает
        // (синтаксис появился только в 12c). Пагинация классическим для более старых версий
        // приёмом через ROWNUM в двух вложенных подзапросах.
        $rows = DB::connection('oracle')->select(
            "SELECT * FROM (
                SELECT inner_q.*, ROWNUM AS rn
                  FROM (
                        SELECT cs.sub_id, cs.client_id, cat.node_name AS course_name, css.status_name AS status,
                               cs.start_date, cs.next_date,
                               cl.client_code, cl.client_name, cl.client_last_name, cl.client_middle_name
                          {$from}
                         ORDER BY cs.sub_id DESC
                       ) inner_q
                 WHERE ROWNUM <= :maxRow
             )
             WHERE rn > :rowOffset",
            $bindings + ['maxRow' => $offset + $perPage, 'rowOffset' => $offset]
        );

        $data = collect($rows)->map(fn ($row) => [
            'sub_id' => (int) $row->sub_id,
            'client_id' => (int) $row->client_id,
            'client_code' => $row->client_code,
            'client_name' => trim("{$row->client_last_name} {$row->client_name} {$row->client_middle_name}"),
            'course_name' => $row->course_name,
            'status' => $row->status,
            'start_date' => $row->start_date ? Carbon::parse($row->start_date)->format('d.m.Y') : null,
            'next_send_date' => $row->next_date ? Carbon::parse($row->next_date)->format('d.m.Y') : null,
        ]);

        return response()->json([
            'data' => $data,
            'current_page' => $page,
            'last_page' => max(1, (int) ceil($total / $perPage)),
            'per_page' => $perPage,
            'total' => $total,
        ]);
    }

    /**
     * Контейнеры (посылки) по курсу (подписке) + план будущих отправок (см.
     * CourseScheduleCalculator) — виртуальные строки, для которых контейнера ещё нет.
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
            'cost_estimated' => false,
            'send_date' => $container->send_date?->format('d.m.Y'),
            'virtual' => false,
            'units' => null,
            ...$this->balance->forCourse($subId, $container->container_id),
        ]);

        // container_cost у плановых строк — оценка (см. CourseScheduleCalculator), не факт:
        // тариф мог измениться к моменту реальной отправки. cost_estimated — фронту, чтобы
        // показать это отличимо от реальной суммы (напр. префиксом "≈"), а не как точную цифру.
        $planned = collect($this->schedule->project($subId))->map(fn (array $item) => [
            'container_id' => null,
            'container_code' => null,
            'status' => 'Запланировано',
            'container_cost' => $item['estimated_cost'],
            'cost_estimated' => true,
            'send_date' => $item['date']->format('d.m.Y'),
            'virtual' => true,
            'units' => $item['units'],
            'invoiced' => 0.0,
            'received' => 0.0,
            'returned' => 0.0,
            'lost' => 0.0,
            'balance' => 0.0,
        ]);

        return response()->json(['containers' => $rows->concat($planned)]);
    }
}

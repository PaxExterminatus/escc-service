<?php

namespace App\Domain\App\Container\Controllers;

use App\Domain\App\Container\Enums\ContainerStatusEnum;
use App\Domain\App\Container\Models\Container;
use App\Domain\App\Container\Requests\SearchContainerRequest;
use App\Domain\App\Container\Resources\ContainerResource;
use App\Domain\App\Course\Services\CourseBalanceCalculator;
use App\Domain\Messages\Models\ClientCommunication;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ContainerController extends Controller
{
    public function __construct(protected CourseBalanceCalculator $balance)
    {
    }

    /**
     * Container
     *
     * Get basic container info
     */
    public function show(int $id): ContainerResource
    {
        $container = Container::where('container_id', $id)->firstOrFail();

        return ContainerResource::make($this->withCommunication($container));
    }

    /**
     * Подробная финансовая информация по контейнеру:
     *  - начислено/оплачено/возврат/потери/баланс — та же формула, что и на странице курса
     *    (см. CourseBalanceCalculator, CourseController::containers()), с фильтром по одному
     *    конкретному контейнеру;
     *  - почтовые расходы — CONTAINER.POST_FEE (фактическая стоимость пересылки) и
     *    POST_FEE_CLIENT (что выставлено клиенту за пересылку — обычно совпадает, но не всегда,
     *    напр. при акциях с бесплатной доставкой);
     *  - содержимое — строки CLIENT_BASKET, отправленные именно в этом контейнере (в контейнере
     *    может быть один урок или несколько — CLIENT_BASKET иерархична: PARENT_ID указывает на
     *    строку самого курса/подписки, CONTAINER_ID у дочерних строк — на конкретную отправку).
     */
    public function finance(int $id): JsonResponse
    {
        $container = Container::where('container_id', $id)->firstOrFail();

        abort_if($container->sub_id === null, 422, 'Контейнер не привязан к курсу — баланс считать не по чему.');

        $items = DB::connection('oracle')->select(
            'SELECT cb.item_id, cb.item_price, cb.item_cost, cb.item_discount, cat.node_name
               FROM client_basket cb
               JOIN catalogue cat ON cat.node_id = cb.node_id
              WHERE cb.container_id = :containerId
              ORDER BY cb.item_id',
            ['containerId' => $id]
        );

        return response()->json([
            ...$this->balance->forCourse((int) $container->sub_id, $id),
            'postage' => [
                'fee' => (float) $container->post_fee,
                'fee_client' => (float) $container->post_fee_client,
            ],
            'items' => collect($items)->map(fn ($row) => [
                'name' => $row->node_name,
                'price' => (float) $row->item_price,
                'cost' => (float) $row->item_cost,
                'discount' => (float) $row->item_discount,
            ]),
        ]);
    }

    /** Поиск контейнеров по набору необязательных параметров, с пагинацией */
    public function search(SearchContainerRequest $request): JsonResponse
    {
        $query = Container::query()
            ->leftJoin('CLIENT', 'CLIENT.client_id', '=', 'CONTAINER.client_id')
            ->select('CONTAINER.*', 'CLIENT.client_code', 'CLIENT.client_name', 'CLIENT.client_last_name', 'CLIENT.client_middle_name')
            ->when($request->container_code, fn ($q, $v) => $q->where('CONTAINER.container_code', 'like', "%{$v}%"))
            ->when($request->client_id, fn ($q, $v) => $q->where('CONTAINER.client_id', $v))
            ->when($request->client_code, fn ($q, $v) => $q->where('CLIENT.client_code', 'like', "%{$v}%"))
            ->when($request->client_name, function ($q, $v) {
                $q->where(function ($q2) use ($v) {
                    $q2->where('CLIENT.client_name', 'like', "%{$v}%")
                        ->orWhere('CLIENT.client_last_name', 'like', "%{$v}%")
                        ->orWhere('CLIENT.client_middle_name', 'like', "%{$v}%");
                });
            })
            ->when($request->status_id, fn ($q, $v) => $q->where('CONTAINER.status_id', $v))
            ->when($request->send_date_from, fn ($q, $v) => $q->whereDate('CONTAINER.send_date', '>=', $v))
            ->when($request->send_date_to, fn ($q, $v) => $q->whereDate('CONTAINER.send_date', '<=', $v))
            ->orderByDesc('CONTAINER.container_id');

        $page = $query->paginate(50, page: $request->integer('page', 1));

        $page->getCollection()->transform(fn (Container $container) => [
            'id' => $container->container_id,
            'code' => $container->container_code,
            'status' => $container->status_id,
            'cost' => (float) $container->container_cost,
            'send_date' => $container->send_date?->format('d.m.Y'),
            'client_id' => $container->client_id,
            'client_code' => $container->client_code,
            'client_name' => trim("{$container->client_last_name} {$container->client_name} {$container->client_middle_name}"),
        ]);

        return response()->json($page);
    }

    /**
     * Email/согласие клиента — оператору на странице контейнера нужно видеть, куда реально
     * уйдёт счёт и есть ли согласие, прежде чем нажимать "Email" (см. ContainerResource).
     */
    protected function withCommunication(Container $container): Container
    {
        $communication = ClientCommunication::find($container->client_id);

        $container->client_email = $communication?->client_email;
        $container->email_allowed = (bool) $communication?->subscriber_email_status;

        return $container;
    }

    /**
     * Container: stop
     *
     * REV_CONST.boxStatusStopped = 1
     */
    public function stop(int $id): ContainerResource
    {
        return $this->applyStatus($id, ContainerStatusEnum::stopped);
    }

    /**
     * Container: start
     *
     * REV_CONST.boxStatusInProgress = 2
     */
    public function start(int $id): ContainerResource
    {
        return $this->applyStatus($id, ContainerStatusEnum::in_progress);
    }

    /**
     * Container: set arbitrary status by REV_CONST id (полный список — все статусы,
     * не только stop/start)
     */
    public function setStatus(int $id, int $statusId): ContainerResource
    {
        $status = ContainerStatusEnum::tryFrom($statusId);

        abort_if($status === null, 422, "Unknown container status id: {$statusId}");

        return $this->applyStatus($id, $status);
    }

    protected function applyStatus(int $id, ContainerStatusEnum $status): ContainerResource
    {
        $container = Container::where('container_id', $id)->firstOrFail();

        $container->status_id = $status->value;
        $container->save();

        return ContainerResource::make($this->withCommunication($container));
    }
}

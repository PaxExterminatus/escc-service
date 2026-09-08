<?php

namespace App\Domain\App\Container\Controllers;

use App\Domain\App\Container\Enums\ContainerStatusEnum;
use App\Domain\App\Container\Models\Container;
use App\Domain\App\Container\Resources\ContainerResource;
use App\Http\Controllers\Controller;

class ContainerController extends Controller
{
    /**
     * Container
     *
     * Get basic container info
     */
    public function show(int $id): ContainerResource
    {
        $container = Container::where('container_id', $id)->firstOrFail();

        return ContainerResource::make($container);
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
        $name = ContainerStatusEnum::name($statusId);

        abort_if($name === null, 422, "Unknown container status id: {$statusId}");

        $container = Container::where('container_id', $id)->firstOrFail();

        $container->status_id = $name;
        $container->save();

        return ContainerResource::make($container);
    }

    protected function applyStatus(int $id, ContainerStatusEnum $status): ContainerResource
    {
        $container = Container::where('container_id', $id)->firstOrFail();

        $container->status_id = $status->value;
        $container->save();

        return ContainerResource::make($container);
    }
}

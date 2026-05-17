<?php

namespace App\Domain\App\Container\Enums;

enum ContainerStatusEnum: string
{
    case stopped = 'Stopped';
    case in_progress = 'InProgress';
    case error = 'Error';
    case canceled = 'Canceled';
    case ready = 'Ready';
    case sent = 'Sent';
    case temporary = 'Temporary';

    static function name(int $id): ?string
    {
        if ($id === 1) return ContainerStatusEnum::stopped->value;
        if ($id === 2) return ContainerStatusEnum::in_progress->value;
        if ($id === 3) return ContainerStatusEnum::error->value;
        if ($id === 4) return ContainerStatusEnum::canceled->value;
        if ($id === 45) return ContainerStatusEnum::ready->value;
        if ($id === 70) return ContainerStatusEnum::sent->value;
        if ($id === -1) return ContainerStatusEnum::temporary->value;
        return null;
    }

    static function id(string $name): ?int
    {
        $lower = strtolower($name);

        if ($lower === strtolower(ContainerStatusEnum::stopped->value)) return 1;
        if ($lower === strtolower(ContainerStatusEnum::in_progress->value)) return 2;
        if ($lower === strtolower(ContainerStatusEnum::error->value)) return 3;
        if ($lower === strtolower(ContainerStatusEnum::canceled->value)) return 4;
        if ($lower === strtolower(ContainerStatusEnum::ready->value)) return 45;
        if ($lower === strtolower(ContainerStatusEnum::sent->value)) return 70;
        if ($lower === strtolower(ContainerStatusEnum::temporary->value)) return -1;
        return null;
    }
}

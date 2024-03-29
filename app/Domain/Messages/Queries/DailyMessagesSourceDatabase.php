<?php

namespace App\Domain\Messages\Queries;

use App\Base\Repositories\DatabaseQuery;
use App\Domain\Messages\Models\DailyMessage;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @method get(): Collection<DailyMessage>
 */
class DailyMessagesSourceDatabase extends DatabaseQuery
{
    use DailyMessagesParamsTrait;

    protected function base(): EloquentBuilder|QueryBuilder
    {
        return DailyMessage::query();
    }

    protected function apply(): static
    {
        if ($this->type)
            $this->query->where('type', Str::upper($this->type));

        return $this;
    }
}
